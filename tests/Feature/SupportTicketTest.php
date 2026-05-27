<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\OtpOrder;
use App\Models\OtpService;
use App\Models\PaymentInvoice;
use App\Models\Provider;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Support\SupportTicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_view_reply_and_close_support_ticket(): void
    {
        $user = User::factory()->create();
        $invoice = PaymentInvoice::create([
            'invoice_no' => 'TOPUP-SUPPORT',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'idempotency_key' => 'idem-support',
            'amount' => '10000.00',
            'net_amount' => '10000.00',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('support.store'), [
                'category' => 'payment',
                'subject' => 'Saldo belum masuk',
                'message' => 'Saya sudah bayar invoice tetapi saldo belum masuk.',
                'payment_invoice_id' => $invoice->id,
            ])
            ->assertRedirect();

        $ticket = SupportTicket::firstOrFail();

        $this->assertSame('open', $ticket->status);
        $this->assertSame($invoice->id, $ticket->payment_invoice_id);
        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get(route('support.show', $ticket))
            ->assertOk()
            ->assertSee('Saldo belum masuk')
            ->assertSee('Saya sudah bayar invoice');

        $this->actingAs($user)
            ->post(route('support.reply', $ticket), ['message' => 'Mohon dicek kembali.'])
            ->assertRedirect();

        $this->assertSame(2, $ticket->messages()->count());

        $this->actingAs($user)
            ->post(route('support.close', $ticket))
            ->assertRedirect();

        $this->assertSame('closed', $ticket->fresh()->status);
    }

    public function test_user_cannot_attach_another_users_invoice_or_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $invoice = PaymentInvoice::create([
            'invoice_no' => 'TOPUP-OTHER',
            'user_id' => $otherUser->id,
            'provider' => 'dompetx',
            'idempotency_key' => 'idem-other',
            'amount' => '10000.00',
            'net_amount' => '10000.00',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('support.store'), [
                'category' => 'payment',
                'subject' => 'Invoice orang lain',
                'message' => 'Ini harus ditolak karena invoice bukan milik saya.',
                'payment_invoice_id' => $invoice->id,
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('support_tickets', 0);
    }

    public function test_admin_can_reply_to_support_ticket(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->superAdmin()->create();
        $ticket = app(SupportTicketService::class)->create($user, [
            'category' => 'order',
            'subject' => 'OTP belum masuk',
            'message' => 'Order saya belum menerima OTP.',
        ]);

        app(SupportTicketService::class)->adminReply($ticket, $admin, 'Kami cek ordernya dulu.');

        $ticket->refresh();

        $this->assertSame('pending_user', $ticket->status);
        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'is_admin' => true,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'support.ticket_replied']);
    }

    public function test_admin_can_view_support_ticket_resource(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->superAdmin()->create();
        $ticket = app(SupportTicketService::class)->create($user, [
            'category' => 'account',
            'subject' => 'Butuh bantuan akun',
            'message' => 'Saya butuh bantuan dengan akun saya.',
        ]);

        $this->actingAs($admin)
            ->get('/admin/support-tickets')
            ->assertOk()
            ->assertSee($ticket->ticket_no);

        $this->actingAs($admin)
            ->get('/admin/support-tickets/'.$ticket->id)
            ->assertOk()
            ->assertSee('Butuh bantuan akun');
    }

    public function test_support_create_page_lists_user_orders_and_invoices(): void
    {
        $user = User::factory()->create();
        $order = $this->orderFor($user);
        $invoice = PaymentInvoice::create([
            'invoice_no' => 'TOPUP-LISTED',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'idempotency_key' => 'idem-listed',
            'amount' => '10000.00',
            'net_amount' => '10000.00',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('support.create'))
            ->assertOk()
            ->assertSee($order->order_no)
            ->assertSee($invoice->invoice_no);
    }

    private function orderFor(User $user): OtpOrder
    {
        $provider = Provider::create(['code' => 'smsbower', 'name' => 'SMSBower', 'is_active' => true]);
        $service = OtpService::create(['provider_id' => $provider->id, 'provider_code' => 'wa', 'name' => 'WhatsApp']);
        $country = Country::create(['provider_id' => $provider->id, 'provider_code' => '6', 'name' => 'Indonesia']);

        return OtpOrder::create([
            'order_no' => 'OTP-SUPPORT-LISTED',
            'user_id' => $user->id,
            'provider_id' => $provider->id,
            'otp_service_id' => $service->id,
            'country_id' => $country->id,
            'provider_cost' => '0.1000',
            'selling_price' => '2000.00',
            'margin_amount' => '400.00',
            'status' => 'failed',
        ]);
    }
}
