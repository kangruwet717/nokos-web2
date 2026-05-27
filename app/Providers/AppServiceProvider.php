<?php

namespace App\Providers;

use App\Services\Payments\DompetxClient;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Providers\SmsbowerClient;
use App\Services\Providers\SmsProviderInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, DompetxClient::class);
        $this->app->bind(SmsProviderInterface::class, SmsbowerClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('topup-create', fn (Request $request) => [
            Limit::perMinute(5)->by($request->user()?->id ?: $request->ip()),
            Limit::perHour(20)->by($request->user()?->id ?: $request->ip()),
        ]);

        RateLimiter::for('payment-status', fn (Request $request) => [
            Limit::perMinute(12)->by($request->user()?->id ?: $request->ip()),
        ]);

        RateLimiter::for('otp-order', fn (Request $request) => [
            Limit::perMinute(3)->by($request->user()?->id ?: $request->ip()),
            Limit::perHour((int) env('OTP_MAX_ORDERS_PER_HOUR', 5))->by($request->user()?->id ?: $request->ip()),
        ]);

        RateLimiter::for('otp-status', fn (Request $request) => [
            Limit::perMinute(20)->by($request->user()?->id ?: $request->ip()),
        ]);

        RateLimiter::for('otp-action', fn (Request $request) => [
            Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()),
        ]);
    }
}
