<?php

namespace App\Http\Controllers;

use App\Models\PaymentInvoice;
use App\Models\OtpOrder;
use App\Models\SupportTicket;
use App\Services\Support\SupportTicketService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = $request->user()
            ->supportTickets()
            ->latest()
            ->paginate(15);

        return view('support.index', compact('tickets'));
    }

    public function create(Request $request): View
    {
        return view('support.create', [
            'orders' => $request->user()->otpOrders()->latest()->limit(20)->get(['id', 'order_no']),
            'invoices' => $request->user()->paymentInvoices()->latest()->limit(20)->get(['id', 'invoice_no']),
        ]);
    }

    public function store(Request $request, SupportTicketService $support): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(['payment', 'order', 'refund', 'account', 'abuse', 'other'])],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'otp_order_id' => ['nullable', 'integer'],
            'payment_invoice_id' => ['nullable', 'integer'],
        ]);

        $data['otp_order_id'] = $this->ownedOrderId($request, $data['otp_order_id'] ?? null);
        $data['payment_invoice_id'] = $this->ownedInvoiceId($request, $data['payment_invoice_id'] ?? null);

        $ticket = $support->create($request->user(), $data);

        return redirect()->route('support.show', $ticket)->with('status', 'Ticket support dibuat.');
    }

    public function show(Request $request, SupportTicket $ticket): View
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);

        $ticket->load(['messages.user', 'otpOrder', 'paymentInvoice']);

        return view('support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket, SupportTicketService $support): RedirectResponse
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        try {
            $support->userReply($ticket, $request->user(), $data['message']);
        } catch (\Throwable $exception) {
            return back()->withErrors(['message' => $exception->getMessage()]);
        }

        return back()->with('status', 'Balasan terkirim.');
    }

    public function close(Request $request, SupportTicket $ticket, SupportTicketService $support): RedirectResponse
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);

        $support->close($ticket, $request->user());

        return back()->with('status', 'Ticket ditutup.');
    }

    private function ownedOrderId(Request $request, ?int $orderId): ?int
    {
        if (! $orderId) {
            return null;
        }

        abort_unless(OtpOrder::whereKey($orderId)->where('user_id', $request->user()->id)->exists(), 422);

        return $orderId;
    }

    private function ownedInvoiceId(Request $request, ?int $invoiceId): ?int
    {
        if (! $invoiceId) {
            return null;
        }

        abort_unless(PaymentInvoice::whereKey($invoiceId)->where('user_id', $request->user()->id)->exists(), 422);

        return $invoiceId;
    }
}
