<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_can_not_access_transaction_pages(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('wallet.history'))->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get(route('topup.index'))->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get(route('otp.index'))->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get(route('profile.edit'))->assertOk();
    }

    public function test_topup_create_route_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->actingAs($user)->post(route('topup.store'), ['amount' => 9999])->assertSessionHasErrors('amount');
        }

        $this->actingAs($user)->post(route('topup.store'), ['amount' => 9999])->assertTooManyRequests();
    }

    public function test_otp_order_route_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->actingAs($user)->post(route('otp.orders.store'), ['service_price_id' => 999])->assertSessionHasErrors('service_price_id');
        }

        $this->actingAs($user)->post(route('otp.orders.store'), ['service_price_id' => 999])->assertTooManyRequests();
    }
}
