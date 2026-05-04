<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Wallet;
use App\Models\PortfolioSnapshot;
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

            // attach for frontend
            $portfolio->price = $price;
            $portfolio->value = $value;

            $totalCrypto += $value;
        }

        $cash = Wallet::where('user_id', Auth::id())
            ->value('cash') ?? 0;

        $totalBalance = $totalCrypto + (float) $cash;

        $snapshots = PortfolioSnapshot::where('user_id', Auth::id())
            ->whereNotNull('date')
            ->orderBy('date', 'asc')
            ->take(7)
            ->get()
            ->map(fn($s) => [
                'date' => $s->date->format('Y-m-d'),
                'value' => (float) $s->total_value
            ]);

        $percentChange = 0;
        $changeAmount = 0;

        if ($snapshots->count() >= 2) {
            $previousValue = (float) $snapshots[$snapshots->count() - 2]['value'];
            $todayValue = (float) $snapshots[$snapshots->count() - 1]['value'];

            $changeAmount = $todayValue - $previousValue;

            if ($previousValue > 0) {
                $percentChange = ($changeAmount / $previousValue) * 100;
            }
        }

        $percentChange = round($percentChange, 2);
        $changeAmount = round($changeAmount, 2);



        return Inertia::render('Dashboard', [
            'portfolios' => $portfolios,
            'cash' => (float) $cash,
            'totalBalance' => $totalBalance,
            'snapshots' => $snapshots,
            'percentChange' => $percentChange,
            'changeAmount' => $changeAmount
        ]);
    }
}
