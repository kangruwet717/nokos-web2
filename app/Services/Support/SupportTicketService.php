<?php

namespace App\Services\Support;

use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SupportTicketService
{
    public function __construct(
        private readonly AuditLogService $audit,
    ) {}

    public function create(User $user, array $data): SupportTicket
    {
        return DB::transaction(function () use ($user, $data): SupportTicket {
            $ticket = SupportTicket::create([
                'ticket_no' => $this->generateTicketNo(),
                'user_id' => $user->id,
                'otp_order_id' => $data['otp_order_id'] ?? null,
                'payment_invoice_id' => $data['payment_invoice_id'] ?? null,
                'category' => $data['category'],
                'subject' => $data['subject'],
                'status' => 'open',
                'priority' => 'normal',
                'last_replied_at' => now(),
            ]);

            $ticket->messages()->create([
                'user_id' => $user->id,
                'is_admin' => false,
                'message' => $data['message'],
            ]);

            $this->audit->record('support.ticket_created', $user, $ticket, [
                'category' => $ticket->category,
            ]);

            return $ticket;
        });
    }

    public function userReply(SupportTicket $ticket, User $user, string $message): SupportTicket
    {
        if ($ticket->user_id !== $user->id) {
            throw new RuntimeException('Ticket tidak ditemukan.');
        }

        if ($ticket->isClosed()) {
            throw new RuntimeException('Ticket sudah ditutup.');
        }

        return DB::transaction(function () use ($ticket, $user, $message): SupportTicket {
            $ticket->messages()->create([
                'user_id' => $user->id,
                'is_admin' => false,
                'message' => $message,
            ]);

            $ticket->forceFill([
                'status' => 'open',
                'last_replied_at' => now(),
            ])->save();

            return $ticket->refresh();
        });
    }

    public function adminReply(SupportTicket $ticket, User $admin, string $message): SupportTicket
    {
        if (! $admin->isAdmin()) {
            throw new RuntimeException('Admin access required.');
        }

        if ($ticket->isClosed()) {
            throw new RuntimeException('Ticket sudah ditutup.');
        }

        return DB::transaction(function () use ($ticket, $admin, $message): SupportTicket {
            $ticket->messages()->create([
                'user_id' => $admin->id,
                'is_admin' => true,
                'message' => $message,
            ]);

            $ticket->forceFill([
                'status' => 'pending_user',
                'last_replied_at' => now(),
            ])->save();

            $this->audit->record('support.ticket_replied', $admin, $ticket);

            return $ticket->refresh();
        });
    }

    public function close(SupportTicket $ticket, User $actor): SupportTicket
    {
        if ($ticket->isClosed()) {
            return $ticket;
        }

        $ticket->forceFill([
            'status' => 'closed',
            'closed_at' => now(),
        ])->save();

        $this->audit->record('support.ticket_closed', $actor, $ticket);

        return $ticket->refresh();
    }

    private function generateTicketNo(): string
    {
        do {
            $ticketNo = 'SUP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5));
        } while (SupportTicket::where('ticket_no', $ticketNo)->exists());

        return $ticketNo;
    }
}
