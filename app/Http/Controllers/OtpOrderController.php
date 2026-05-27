<?php

namespace App\Http\Controllers;

use App\Models\OtpOrder;
use App\Models\ServicePrice;
use App\Services\Orders\OtpOrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpOrderController extends Controller
{
    public function store(Request $request, OtpOrderService $orders): RedirectResponse
    {
        $key = 'otp-order:'.$request->user()->id;
        $maxAttempts = (int) env('OTP_MAX_ORDERS_PER_HOUR', 5);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                'service_price_id' => 'Limit order sementara tercapai. Coba lagi nanti.',
            ]);
        }

        $data = $request->validate([
            'service_price_id' => ['required', 'integer', 'exists:service_prices,id'],
        ]);

        $price = ServicePrice::with(['provider', 'otpService', 'country'])->findOrFail($data['service_price_id']);
        try {
            $order = $orders->create($request->user(), $price);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'service_price_id' => $exception->getMessage(),
            ]);
        }

        RateLimiter::hit($key, 3600);

        return redirect()->route('otp.orders.show', $order);
    }

    public function index(Request $request): View
    {
        $orders = $request->user()
            ->otpOrders()
            ->with(['otpService', 'country'])
            ->latest()
            ->paginate(20);

        return view('otp.orders.index', compact('orders'));
    }

    public function show(Request $request, OtpOrder $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load(['otpService', 'country', 'statusLogs' => fn ($query) => $query->latest()]);

        return view('otp.orders.show', compact('order'));
    }

    public function status(Request $request, OtpOrder $order, OtpOrderService $orders): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order = $orders->refreshStatus($order);

        return response()->json([
            'status' => $order->status,
            'sms_code' => $order->sms_code,
            'completed_at' => $order->completed_at?->toISOString(),
            'cancelled_at' => $order->cancelled_at?->toISOString(),
        ]);
    }

    public function refresh(Request $request, OtpOrder $order, OtpOrderService $orders): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $orders->refreshStatus($order);

        return back()->with('status', 'Status order diperbarui.');
    }

    public function cancel(Request $request, OtpOrder $order, OtpOrderService $orders): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $orders->cancel($order, $request->user());

        return back()->with('status', 'Order dibatalkan dan saldo tertahan dikembalikan.');
    }
}
