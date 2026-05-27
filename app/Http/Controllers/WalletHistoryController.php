<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WalletHistoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $transactions = $request->user()
            ->walletTransactions()
            ->latest()
            ->paginate(20);

        return view('wallet.history', compact('transactions'));
    }
}
