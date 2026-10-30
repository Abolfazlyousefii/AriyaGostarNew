<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Inventory\CustomerLedger;
use App\Models\WalletHistory;
use Illuminate\Http\Request;

class WalletHistoryController extends Controller {
    public function __construct() {
        $this->middleware('can:payments.wallet-histories.index');
    }

    public function index() {
        $histories = CustomerLedger::with('customer')
            ->latest()
            ->paginate(20);

        return view('back.wallet-histories.index', compact('histories'));
    }

    public function show( CustomerLedger $wallet_history ) {
        $wallet_history->load('customer');

        return view('back.wallet-histories.show', compact('wallet_history'));
    }
}
