<?php

use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminWalletAdjustmentController;
use App\Http\Controllers\DompetxWebhookController;
use App\Http\Controllers\OtpOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderCatalogController;
use App\Http\Controllers\SmsbowerWebhookController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\TopUpController;
use App\Http\Controllers\WalletHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::view('/terms', 'legal.terms')->name('legal.terms');
Route::view('/privacy', 'legal.privacy')->name('legal.privacy');
Route::view('/refund-policy', 'legal.refund')->name('legal.refund');
Route::view('/acceptable-use', 'legal.acceptable-use')->name('legal.acceptable-use');
Route::view('/contact', 'legal.contact')->name('legal.contact');

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    return view('dashboard', [
        'recentTransactions' => $user->walletTransactions()->latest()->limit(5)->get(),
        'recentOrders' => $user->otpOrders()->with(['otpService', 'country'])->latest()->limit(5)->get(),
        'activeOrders' => $user->otpOrders()
            ->with(['otpService', 'country'])
            ->whereIn('status', ['pending', 'waiting_sms'])
            ->latest()
            ->limit(3)
            ->get(),
        'pendingInvoices' => $user->paymentInvoices()->where('status', 'pending')->latest()->limit(3)->get(),
    ]);
})->middleware(['auth', 'active'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/wallet/history', WalletHistoryController::class)->name('wallet.history');
    Route::get('/topup', [TopUpController::class, 'index'])->name('topup.index');
    Route::post('/topup', [TopUpController::class, 'store'])->middleware('throttle:topup-create')->name('topup.store');
    Route::get('/topup/{invoice}', [TopUpController::class, 'show'])->name('topup.show');
    Route::get('/topup/{invoice}/status', [TopUpController::class, 'status'])->middleware('throttle:payment-status')->name('topup.status');
    Route::post('/topup/{invoice}/reconcile', [TopUpController::class, 'reconcile'])->middleware('throttle:payment-status')->name('topup.reconcile');
    Route::get('/otp', [ProviderCatalogController::class, 'index'])->name('otp.index');
    Route::get('/otp/services', [ProviderCatalogController::class, 'services'])->name('otp.services');
    Route::get('/otp/prices', [ProviderCatalogController::class, 'prices'])->name('otp.prices');
    Route::post('/otp/refresh-current', [ProviderCatalogController::class, 'refreshCurrent'])->middleware('throttle:6,1')->name('otp.refresh-current');
    Route::post('/otp/orders', [OtpOrderController::class, 'store'])->middleware('throttle:otp-order')->name('otp.orders.store');
    Route::get('/otp/orders', [OtpOrderController::class, 'index'])->name('otp.orders.index');
    Route::get('/otp/orders/{order}', [OtpOrderController::class, 'show'])->name('otp.orders.show');
    Route::get('/otp/orders/{order}/status', [OtpOrderController::class, 'status'])->middleware('throttle:otp-status')->name('otp.orders.status');
    Route::post('/otp/orders/{order}/refresh', [OtpOrderController::class, 'refresh'])->middleware('throttle:otp-status')->name('otp.orders.refresh');
    Route::post('/otp/orders/{order}/cancel', [OtpOrderController::class, 'cancel'])->middleware('throttle:otp-action')->name('otp.orders.cancel');
    Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
    Route::get('/support/create', [SupportTicketController::class, 'create'])->name('support.create');
    Route::post('/support', [SupportTicketController::class, 'store'])->middleware('throttle:10,1')->name('support.store');
    Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->middleware('throttle:20,1')->name('support.reply');
    Route::post('/support/{ticket}/close', [SupportTicketController::class, 'close'])->middleware('throttle:10,1')->name('support.close');
});

Route::middleware(['auth', 'active', 'admin'])
    ->prefix('admin/reports')
    ->name('admin.reports.')
    ->group(function () {
        Route::get('/', [AdminReportController::class, 'index'])->name('index');
        Route::get('/payment-invoices.csv', [AdminReportController::class, 'paymentInvoices'])->name('payment-invoices');
        Route::get('/wallet-transactions.csv', [AdminReportController::class, 'walletTransactions'])->name('wallet-transactions');
        Route::get('/otp-orders.csv', [AdminReportController::class, 'otpOrders'])->name('otp-orders');
        Route::get('/profit.csv', [AdminReportController::class, 'profit'])->name('profit');
    });

Route::middleware(['auth', 'active', 'admin'])
    ->prefix('admin-tools/users/{user}/wallet-adjustment')
    ->name('admin.wallet-adjustments.')
    ->group(function () {
        Route::get('/', [AdminWalletAdjustmentController::class, 'edit'])->name('edit');
        Route::post('/', [AdminWalletAdjustmentController::class, 'update'])->name('update');
    });

Route::post('/webhooks/dompetx', DompetxWebhookController::class)->name('webhooks.dompetx');
Route::post('/webhooks/smsbower', SmsbowerWebhookController::class)->name('webhooks.smsbower');

require __DIR__.'/auth.php';
