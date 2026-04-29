<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\LunoService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $portfolios = Portfolio::with('asset')
            ->where('user_id', Auth::id())
            ->get();

        $luno = new LunoService();
        $totalCrypto = 0;

        foreach ($portfolios as $portfolio) {

            $symbol = $portfolio->asset->symbol;

            // convert symbol → pair
            $pair = $symbol === 'BTC' ? 'XBTMYR' : $symbol . 'MYR';

            $ticker = $luno->getTicker($pair);

            $price = (float) ($ticker['last_trade'] ?? 0);

            $value = $portfolio->amount * $price;

            $transactions = Transaction::where('user_id', Auth::id())
                ->where('asset_id', $portfolio->asset_id)
                ->where('type', 'buy')
                ->get();

            $totalAmount = $transactions->sum('amount');

            $totalCost = $transactions->sum(function ($t) {
                return $t->amount * $t->price;
            });

            $avgPrice = $totalAmount > 0 ? $totalCost / $totalAmount : 0;

            // attach to portfolio
            $portfolio->avg_buy_price = $avgPrice;

            // attach for frontend
            $portfolio->price = $price;
            $portfolio->value = $value;

            $totalCrypto += $value;
        }


        $cash = Wallet::where('user_id', Auth::id())
            ->value('cash') ?? 0;

        $totalBalance = $totalCrypto + (float) $cash;

        return Inertia::render('Dashboard', [
            'portfolios' => $portfolios,
            'cash' => (float) $cash,
            'totalBalance' => $totalBalance
        ]);
    }
}
