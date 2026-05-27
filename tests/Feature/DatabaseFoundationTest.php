<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_foundation_tables_have_required_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('users', [
            'role',
            'status',
            'balance',
            'reserved_balance',
            'kyc_status',
            'risk_score',
            'last_login_at',
            'terms_accepted_at',
            'terms_version',
            'terms_accepted_ip',
        ]));

        $this->assertTrue(Schema::hasColumns('wallet_transactions', [
            'user_id',
            'type',
            'direction',
            'amount',
            'balance_before',
            'balance_after',
            'reserved_before',
            'reserved_after',
            'reference_type',
            'reference_id',
            'created_by_admin_id',
        ]));

        $this->assertTrue(Schema::hasColumns('audit_logs', [
            'actor_user_id',
            'actor_role',
            'action',
            'target_type',
            'target_id',
            'metadata',
        ]));

        $this->assertTrue(Schema::hasColumns('providers', [
            'code',
            'name',
            'base_url',
            'is_active',
            'last_balance',
            'balance_checked_at',
        ]));

        $this->assertTrue(Schema::hasColumns('service_prices', [
            'provider_id',
            'otp_service_id',
            'country_id',
            'provider_price_key',
            'provider_price',
            'provider_meta',
            'selling_price',
            'stock_count',
            'last_synced_at',
        ]));

        $this->assertTrue(Schema::hasColumns('otp_orders', [
            'order_no',
            'user_id',
            'provider_id',
            'otp_service_id',
            'country_id',
            'provider_activation_id',
            'phone_number',
            'selling_price',
            'status',
            'sms_code',
            'expires_at',
        ]));

        $this->assertTrue(Schema::hasColumns('provider_webhook_events', [
            'provider',
            'event_id',
            'activation_id',
            'otp_order_id',
            'payload',
            'signature_valid',
            'processed',
            'processed_at',
        ]));

        $this->assertTrue(Schema::hasColumns('support_tickets', [
            'ticket_no',
            'user_id',
            'category',
            'subject',
            'status',
            'last_replied_at',
        ]));

        $this->assertTrue(Schema::hasColumns('support_ticket_messages', [
            'support_ticket_id',
            'user_id',
            'is_admin',
            'message',
        ]));

        $this->assertTrue(Schema::hasColumns('operational_alerts', [
            'type',
            'severity',
            'dedupe_key',
            'title',
            'message',
            'metadata',
            'resolved_at',
            'resolved_by_user_id',
        ]));
    }
}
