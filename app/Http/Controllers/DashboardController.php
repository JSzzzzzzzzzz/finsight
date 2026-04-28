<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $portfolios = Portfolio::with('asset')
            ->where('user_id', Auth::id())
            ->get();

        $cash = Wallet::where('user_id', Auth::id())
            ->value('cash') ?? 0;

        // dd($cash);

        return Inertia::render('Dashboard', [
            'portfolios' => $portfolios,
            'cash' => (float) $cash
        ]);
    }
}
