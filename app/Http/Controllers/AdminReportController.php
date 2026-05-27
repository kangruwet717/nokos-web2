<?php

namespace App\Http\Controllers;

use App\Models\OtpOrder;
use App\Models\PaymentInvoice;
use App\Models\WalletTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index');
    }

    public function paymentInvoices(Request $request): StreamedResponse
    {
        $query = PaymentInvoice::query()->with('user');
        $this->applyDateRange($query, $request);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $this->csv('payment-invoices', [
            'invoice_no',
            'user_email',
            'provider',
            'external_id',
            'amount',
            'fee',
            'net_amount',
            'status',
            'payment_method',
            'paid_at',
            'created_at',
        ], $query->oldest()->cursor()->map(fn (PaymentInvoice $invoice): array => [
            $invoice->invoice_no,
            $invoice->user?->email,
            $invoice->provider,
            $invoice->external_id,
            $invoice->amount,
            $invoice->fee,
            $invoice->net_amount,
            $invoice->status,
            $invoice->payment_method,
            $invoice->paid_at?->toDateTimeString(),
            $invoice->created_at->toDateTimeString(),
        ]));
    }

    public function walletTransactions(Request $request): StreamedResponse
    {
        $query = WalletTransaction::query()->with(['user', 'admin']);
        $this->applyDateRange($query, $request);

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        return $this->csv('wallet-transactions', [
            'created_at',
            'user_email',
            'type',
            'direction',
            'amount',
            'balance_before',
            'balance_after',
            'reserved_before',
            'reserved_after',
            'reference_type',
            'reference_id',
            'admin_email',
            'description',
        ], $query->oldest()->cursor()->map(fn (WalletTransaction $transaction): array => [
            $transaction->created_at->toDateTimeString(),
            $transaction->user?->email,
            $transaction->type,
            $transaction->direction,
            $transaction->amount,
            $transaction->balance_before,
            $transaction->balance_after,
            $transaction->reserved_before,
            $transaction->reserved_after,
            $transaction->reference_type,
            $transaction->reference_id,
            $transaction->admin?->email,
            $transaction->description,
        ]));
    }

    public function otpOrders(Request $request): StreamedResponse
    {
        $query = OtpOrder::query()->with(['user', 'provider', 'otpService', 'country']);
        $this->applyDateRange($query, $request);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $this->csv('otp-orders', [
            'created_at',
            'order_no',
            'user_email',
            'provider',
            'service',
            'country',
            'provider_activation_id',
            'selling_price',
            'provider_cost',
            'margin_amount',
            'status',
            'completed_at',
            'cancelled_at',
            'refunded_at',
        ], $query->oldest()->cursor()->map(fn (OtpOrder $order): array => [
            $order->created_at->toDateTimeString(),
            $order->order_no,
            $order->user?->email,
            $order->provider?->code,
            $order->otpService?->name,
            $order->country?->name,
            $order->provider_activation_id,
            $order->selling_price,
            $order->provider_cost,
            $order->margin_amount,
            $order->status,
            $order->completed_at?->toDateTimeString(),
            $order->cancelled_at?->toDateTimeString(),
            $order->refunded_at?->toDateTimeString(),
        ]));
    }

    public function profit(Request $request): StreamedResponse
    {
        $query = OtpOrder::query()
            ->with(['otpService', 'country'])
            ->whereIn('status', ['success', 'refunded']);
        $this->applyDateRange($query, $request);

        return $this->csv('profit-report', [
            'date',
            'order_no',
            'service',
            'country',
            'status',
            'selling_price',
            'provider_cost',
            'margin_amount',
        ], $query->oldest()->cursor()->map(fn (OtpOrder $order): array => [
            $order->created_at->toDateString(),
            $order->order_no,
            $order->otpService?->name,
            $order->country?->name,
            $order->status,
            $order->selling_price,
            $order->provider_cost,
            $order->margin_amount,
        ]));
    }

    private function applyDateRange(Builder $query, Request $request): void
    {
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date('date_from')->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date('date_to')->endOfDay());
        }
    }

    private function csv(string $name, array $headers, iterable $rows): StreamedResponse
    {
        $filename = $name.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, array_map(fn ($value): string => $this->csvValue($value), $row));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function csvValue(mixed $value): string
    {
        $value = (string) $value;

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "'".$value;
        }

        return $value;
    }
}
