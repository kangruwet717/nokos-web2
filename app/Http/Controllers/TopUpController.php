<?php

namespace App\Http\Controllers;

use App\Models\PaymentInvoice;
use App\Services\Payments\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class TopUpController extends Controller
{
    public function index(Request $request): View
    {
        $invoices = $request->user()
            ->paymentInvoices()
            ->latest()
            ->limit(10)
            ->get();

        return view('topup.index', compact('invoices'));
    }

    public function store(Request $request, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:10000', 'max:10000000'],
        ]);

        $invoice = $payments->createTopUpInvoice($request->user(), (string) $data['amount']);

        return redirect()->route('topup.show', $invoice);
    }

    public function show(Request $request, PaymentInvoice $invoice): View
    {
        abort_unless($invoice->user_id === $request->user()->id, 404);

        return view('topup.show', compact('invoice'));
    }

    public function status(Request $request, PaymentInvoice $invoice): JsonResponse
    {
        abort_unless($invoice->user_id === $request->user()->id, 404);

        return response()->json([
            'status' => $invoice->status,
            'paid_at' => $invoice->paid_at?->toISOString(),
            'balance' => $request->user()->fresh()->balance,
        ]);
    }

    public function reconcile(Request $request, PaymentInvoice $invoice, PaymentService $payments): RedirectResponse
    {
        abort_unless($invoice->user_id === $request->user()->id, 404);

        try {
            $payments->reconcile($invoice);
        } catch (RuntimeException $exception) {
            report($exception);

            return back()->withErrors([
                'payment' => 'Status DompetX belum bisa dicek saat ini. Invoice tetap pending; coba lagi beberapa saat atau tunggu webhook otomatis.',
            ]);
        }

        return back()->with('status', 'Status invoice diperbarui.');
    }
}
