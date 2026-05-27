<?php

namespace Tests\Feature;

use App\Models\PaymentInvoice;
use App\Models\User;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Tests\TestCase;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_topup_invoice(): void
    {
        $this->bindGateway(new class implements PaymentGatewayInterface
        {
            public function createCheckout(array $payload, string $idempotencyKey): array
            {
                return [
                    'id' => 'checkout-123',
                    'status' => 'pending',
                    'payment_link' => 'https://checkout.dompetx.test/checkout-123',
                    'expiresAt' => now()->addDay()->toISOString(),
                ];
            }

            public function getCheckoutDetail(string $checkoutId): array
            {
                return [];
            }

            public function checkCheckoutStatus(string $checkoutId): array
            {
                return [];
            }

            public function cancelCheckout(string $checkoutId): array
            {
                return [];
            }
        });

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/topup', [
            'amount' => 50000,
        ]);

        $invoice = PaymentInvoice::firstOrFail();

        $response->assertRedirect(route('topup.show', $invoice));
        $this->assertSame('pending', $invoice->status);
        $this->assertSame('checkout-123', $invoice->external_id);
        $this->assertSame('https://checkout.dompetx.test/checkout-123', $invoice->payment_url);
    }

    public function test_topup_invoice_accepts_dompetx_payment_url_field(): void
    {
        $this->bindGateway(new class implements PaymentGatewayInterface
        {
            public function createCheckout(array $payload, string $idempotencyKey): array
            {
                return [
                    'id' => 'checkout-456',
                    'status' => 'pending',
                    'payment_url' => 'https://checkout.dompetx.test/checkout-456',
                    'expiresAt' => now()->addDay()->toISOString(),
                ];
            }

            public function getCheckoutDetail(string $checkoutId): array
            {
                return [];
            }

            public function checkCheckoutStatus(string $checkoutId): array
            {
                return [];
            }

            public function cancelCheckout(string $checkoutId): array
            {
                return [];
            }
        });

        $user = User::factory()->create();

        $this->actingAs($user)->post('/topup', ['amount' => 10000]);

        $invoice = PaymentInvoice::firstOrFail();

        $this->assertSame('https://checkout.dompetx.test/checkout-456', $invoice->payment_url);

        $this->actingAs($user)
            ->get(route('topup.show', $invoice))
            ->assertOk()
            ->assertSee('Bayar Sekarang')
            ->assertSee('https://checkout.dompetx.test/checkout-456');
    }

    public function test_topup_show_uses_checkout_url_from_raw_response_as_fallback(): void
    {
        $user = User::factory()->create();
        $invoice = PaymentInvoice::create([
            'invoice_no' => 'TOPUP-RAW-LINK',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'external_id' => 'checkout-raw-link',
            'idempotency_key' => 'idem-raw-link',
            'amount' => '10000.00',
            'net_amount' => '10000.00',
            'status' => 'pending',
            'raw_create_response' => [
                'payment_url' => 'https://checkout.dompetx.test/raw-link',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('topup.show', $invoice))
            ->assertOk()
            ->assertSee('Bayar Sekarang')
            ->assertSee('https://checkout.dompetx.test/raw-link');
    }

    public function test_dompetx_paid_webhook_credits_wallet_once(): void
    {
        $user = User::factory()->create();
        $invoice = PaymentInvoice::create([
            'invoice_no' => 'TOPUP-TEST',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'external_id' => 'checkout-123',
            'idempotency_key' => 'idem-123',
            'amount' => '50000.00',
            'net_amount' => '50000.00',
            'status' => 'pending',
        ]);

        $payload = [
            'data' => [
                'id' => 'checkout-123',
                'amount' => 50000,
                'status' => 'paid',
                'currency' => 'IDR',
                'reference' => $invoice->invoice_no,
            ],
            'eventType' => 'deposit',
            'paymentId' => 'checkout-123',
        ];

        $this->postSignedDompetxWebhook($payload)->assertOk();
        $this->postSignedDompetxWebhook($payload)->assertOk();

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('50000.00', $user->fresh()->balance);
        $this->assertDatabaseCount('wallet_transactions', 1);
        $this->assertDatabaseCount('payment_webhook_events', 1);
    }

    public function test_paid_webhook_with_mismatched_amount_does_not_credit_wallet(): void
    {
        $user = User::factory()->create();
        $invoice = PaymentInvoice::create([
            'invoice_no' => 'TOPUP-MISMATCH',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'external_id' => 'checkout-456',
            'idempotency_key' => 'idem-456',
            'amount' => '50000.00',
            'net_amount' => '50000.00',
            'status' => 'pending',
        ]);

        $this->postSignedDompetxWebhook([
            'data' => [
                'id' => 'checkout-456',
                'amount' => 10000,
                'status' => 'paid',
                'currency' => 'IDR',
                'reference' => $invoice->invoice_no,
            ],
            'eventType' => 'deposit',
            'paymentId' => 'checkout-456',
        ])->assertOk();

        $this->assertSame('pending', $invoice->fresh()->status);
        $this->assertSame('0.00', $user->fresh()->balance);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_dompetx_webhook_with_invalid_signature_is_rejected_without_poisoning_valid_event(): void
    {
        config()->set('services.dompetx.api_key', 'dompetx-test-key');
        config()->set('services.dompetx.webhook_secret', null);

        $user = User::factory()->create();
        $invoice = PaymentInvoice::create([
            'invoice_no' => 'TOPUP-SIGNED',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'external_id' => 'checkout-signed',
            'idempotency_key' => 'idem-signed',
            'amount' => '50000.00',
            'net_amount' => '50000.00',
            'status' => 'pending',
        ]);

        $payload = [
            'data' => [
                'id' => 'checkout-signed',
                'amount' => 50000,
                'status' => 'paid',
                'currency' => 'IDR',
                'reference' => $invoice->invoice_no,
            ],
            'eventType' => 'deposit',
            'paymentId' => 'checkout-signed',
        ];

        $this->withHeaders([
            'X-DOMPAY-Timestamp' => (string) time(),
            'X-DOMPAY-Signature' => 'bad-signature',
        ])->postJson('/webhooks/dompetx', $payload)->assertUnauthorized();

        $this->assertSame('pending', $invoice->fresh()->status);
        $this->assertSame('0.00', $user->fresh()->balance);

        $this->postSignedDompetxWebhook($payload)->assertOk();

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('50000.00', $user->fresh()->balance);
        $this->assertDatabaseHas('payment_webhook_events', ['signature_valid' => false]);
        $this->assertDatabaseHas('payment_webhook_events', ['signature_valid' => true, 'processed' => true]);
    }

    public function test_reconcile_marks_paid_invoice_and_credits_wallet(): void
    {
        $this->bindGateway(new class implements PaymentGatewayInterface
        {
            public function createCheckout(array $payload, string $idempotencyKey): array
            {
                return [];
            }

            public function getCheckoutDetail(string $checkoutId): array
            {
                return [];
            }

            public function checkCheckoutStatus(string $checkoutId): array
            {
                return [
                    'id' => $checkoutId,
                    'amount' => 75000,
                    'status' => 'paid',
                    'type' => 'qr_dynamic',
                    'fee' => 1000,
                ];
            }

            public function cancelCheckout(string $checkoutId): array
            {
                return [];
            }
        });

        $user = User::factory()->create();
        $invoice = PaymentInvoice::create([
            'invoice_no' => 'TOPUP-RECON',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'external_id' => 'checkout-789',
            'idempotency_key' => 'idem-789',
            'amount' => '75000.00',
            'net_amount' => '75000.00',
            'status' => 'pending',
        ]);

        app(PaymentService::class)->reconcile($invoice);

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('75000.00', $user->fresh()->balance);
        $this->assertDatabaseCount('wallet_transactions', 1);
    }

    public function test_reconcile_provider_error_returns_to_invoice_without_crashing(): void
    {
        $this->bindGateway(new class implements PaymentGatewayInterface
        {
            public function createCheckout(array $payload, string $idempotencyKey): array
            {
                return [];
            }

            public function getCheckoutDetail(string $checkoutId): array
            {
                return [];
            }

            public function checkCheckoutStatus(string $checkoutId): array
            {
                throw new RuntimeException('DompetX request failed [500]: {"code":"internal_error","message":"An unexpected error occurred"}');
            }

            public function cancelCheckout(string $checkoutId): array
            {
                return [];
            }
        });

        $user = User::factory()->create();
        $invoice = PaymentInvoice::create([
            'invoice_no' => 'TOPUP-RECON-ERROR',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'external_id' => 'checkout-error',
            'idempotency_key' => 'idem-error',
            'amount' => '75000.00',
            'net_amount' => '75000.00',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('topup.reconcile', $invoice))
            ->assertRedirect()
            ->assertSessionHasErrors('payment');

        $this->assertSame('pending', $invoice->fresh()->status);
        $this->assertSame('0.00', $user->fresh()->balance);
    }

    public function test_pending_payment_reconcile_command_marks_paid_invoice(): void
    {
        $this->bindGateway(new class implements PaymentGatewayInterface
        {
            public function createCheckout(array $payload, string $idempotencyKey): array
            {
                return [];
            }

            public function getCheckoutDetail(string $checkoutId): array
            {
                return [];
            }

            public function checkCheckoutStatus(string $checkoutId): array
            {
                return [
                    'id' => $checkoutId,
                    'amount' => 90000,
                    'status' => 'paid',
                    'type' => 'qr_dynamic',
                ];
            }

            public function cancelCheckout(string $checkoutId): array
            {
                return [];
            }
        });

        $user = User::factory()->create();
        $invoice = PaymentInvoice::create([
            'invoice_no' => 'TOPUP-AUTO-PAID',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'external_id' => 'checkout-auto-paid',
            'idempotency_key' => 'idem-auto-paid',
            'amount' => '90000.00',
            'net_amount' => '90000.00',
            'status' => 'pending',
            'expired_at' => now()->addMinutes(10),
        ]);

        Artisan::call('payments:reconcile-pending', ['--limit' => 10]);

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('90000.00', $user->fresh()->balance);
        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.invoice_paid']);
    }

    public function test_pending_payment_reconcile_command_expires_overdue_invoice(): void
    {
        putenv('PAYMENT_EXPIRE_GRACE_MINUTES=10');
        $_ENV['PAYMENT_EXPIRE_GRACE_MINUTES'] = 10;

        $user = User::factory()->create();
        $invoice = PaymentInvoice::create([
            'invoice_no' => 'TOPUP-AUTO-EXPIRED',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'idempotency_key' => 'idem-auto-expired',
            'amount' => '50000.00',
            'net_amount' => '50000.00',
            'status' => 'pending',
            'expired_at' => now()->subMinutes(11),
        ]);

        Artisan::call('payments:reconcile-pending', ['--limit' => 10]);

        $this->assertSame('expired', $invoice->fresh()->status);
        $this->assertSame('0.00', $user->fresh()->balance);
        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.invoice_expired']);
    }

    public function test_topup_amount_is_limited(): void
    {
        $this->expectException(RuntimeException::class);

        app(PaymentService::class)->createTopUpInvoice(User::factory()->create(), '9999');
    }

    private function bindGateway(PaymentGatewayInterface $gateway): void
    {
        $this->app->instance(PaymentGatewayInterface::class, $gateway);
    }

    private function postSignedDompetxWebhook(array $payload)
    {
        config()->set('services.dompetx.api_key', 'dompetx-test-key');
        config()->set('services.dompetx.webhook_secret', null);

        $timestamp = (string) time();
        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'dompetx-test-key');

        return $this->call(
            'POST',
            '/webhooks/dompetx',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_DOMPAY_TIMESTAMP' => $timestamp,
                'HTTP_X_DOMPAY_SIGNATURE' => $signature,
            ],
            $body,
        );
    }
}
