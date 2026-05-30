<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminWalletAdjustmentController extends Controller
{
    public function edit(User $user): View
    {
        return view('admin.wallet-adjustments.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        app(WalletService::class)->adjustment(
            $user,
            (string) $validated['amount'],
            $validated['reason'],
            $request->user(),
        );

        app(AuditLogService::class)->record('wallet.adjusted', $request->user(), $user, [
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
        ]);

        return redirect()
            ->route('filament.admin.resources.users.index')
            ->with('status', 'Wallet balance adjusted for '.$user->email.'.');
    }
}
