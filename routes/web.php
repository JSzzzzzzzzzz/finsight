<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\LunoService;
use App\Http\Controllers\DashboardController;
use App\Models\TradingPair;
use App\Models\UserTradingPair;



// PUBLIC ROUTE (Landing Page)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

// PROTECTED ROUTES (Must be Logged In)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // 1. SMART DASHBOARD REDIRECT
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth'])
        ->name('dashboard');
    // ==========================================
    //  ADMIN ROUTES
    // ==========================================

    // A. System Health (Dashboard)
    Route::get('/admin/dashboard', function () {
        if (!Auth::user()->is_admin) abort(403, 'Unauthorized');
        return Inertia::render('Admin/ADashboard');
    })->name('admin.dashboard');

    // B. Manage Users
    Route::get('/admin/users', function () {
        if (!Auth::user()->is_admin) abort(403, 'Unauthorized');
        return Inertia::render('Admin/AUsers');
    })->name('admin.users');

    // C. Manage Pairs
    Route::get('/admin/pairs', function () {
        if (!Auth::user()->is_admin) abort(403, 'Unauthorized');

        return Inertia::render('Admin/APairs', [
            'pairs' => TradingPair::latest()->get()
        ]);
    })->name('admin.pairs');

    Route::post('/admin/pairs', function () {
        if (!Auth::user()->is_admin) abort(403, 'Unauthorized');

        request()->validate([
            'symbol' => 'required|string|max:20|unique:trading_pairs,symbol'
        ]);

        TradingPair::create([
            'symbol' => strtoupper(request('symbol')),
            'source' => 'Luno',
            'is_active' => true,
        ]);

        return back();
    })->name('admin.pairs.store');

    Route::delete('/admin/pairs/{pair}', function (TradingPair $pair) {
        if (!Auth::user()->is_admin) abort(403, 'Unauthorized');

        $pair->delete();

        return back();
    })->name('admin.pairs.destroy');

    Route::post('/admin/pairs/import-luno', function () {
        if (!Auth::user()->is_admin) abort(403, 'Unauthorized');

        $luno = new LunoService();
        $data = $luno->getMarkets();

        $topPairs = [
            'XBTMYR',
            'ETHMYR',
            'XRPMYR',
            'SOLMYR',
            'ADAMYR',
            'LINKMYR',
            'AVAXMYR',
            'BCHMYR',
            'LTCMYR',
            'UNIMYR',
        ];

        $markets = collect($data['markets'] ?? [])
            ->filter(fn($market) => in_array($market['market_id'], $topPairs))
            ->sortBy(fn($market) => array_search($market['market_id'], $topPairs));

        foreach ($markets as $market) {
            TradingPair::firstOrCreate(
                ['symbol' => $market['market_id']],
                [
                    'source' => 'Luno',
                    'is_active' => true,
                ]
            );
        }

        return back();
    })->name('admin.pairs.import-luno');

    Route::patch('/admin/pairs/{pair}/toggle', function (TradingPair $pair) {
        if (!Auth::user()->is_admin) abort(403, 'Unauthorized');

        $pair->update([
            'is_active' => !$pair->is_active
        ]);

        return back();
    })->name('admin.pairs.toggle');


    // ==========================================
    //  USER ROUTES
    // ==========================================
    Route::get('/market', function () {
        if (Auth::user()->is_admin) abort(403, 'Unauthorized');

        $luno = new LunoService();

        $selectedPairs = UserTradingPair::with('tradingPair')
            ->where('user_id', Auth::id())
            ->get();

        $marketData = $selectedPairs->map(function ($item) use ($luno) {
            $pair = $item->tradingPair->symbol;
            $ticker = $luno->getTicker($pair);

            return [
                'id' => $item->id,
                'pair' => $pair,
                'symbol' => $pair === 'XBTMYR'
                    ? 'BTC'
                    : str_replace('MYR', '', $pair),
                'display_pair' => $pair === 'XBTMYR'
                    ? 'BTCMYR'
                    : $pair,
                'source' => $item->tradingPair->source,
                'price' => (float) ($ticker['last_trade'] ?? 0),
                'bid' => (float) ($ticker['bid'] ?? 0),
                'ask' => (float) ($ticker['ask'] ?? 0),
            ];
        });

        return Inertia::render('Market', [
            'marketData' => $marketData,
        ]);
    })->name('market');

    Route::get('/settings', function () {
        if (Auth::user()->is_admin) abort(403, 'Unauthorized');

        $selectedPairIds = UserTradingPair::where('user_id', Auth::id())
            ->pluck('trading_pair_id')
            ->toArray();

        return Inertia::render('Settings', [
            'availablePairs' => TradingPair::where('is_active', true)
                ->whereNotIn('id', $selectedPairIds)
                ->orderBy('symbol')
                ->get(),

            'selectedPairs' => UserTradingPair::with('tradingPair')
                ->where('user_id', Auth::id())
                ->join('trading_pairs', 'user_trading_pairs.trading_pair_id', '=', 'trading_pairs.id')
                ->orderBy('trading_pairs.symbol', 'asc')
                ->select('user_trading_pairs.*')
                ->get(),

            'selectedPairIds' => $selectedPairIds,
        ]);
    })->name('settings');

    Route::post('/settings/pairs', function () {
        if (Auth::user()->is_admin) abort(403, 'Unauthorized');

        request()->validate([
            'trading_pair_id' => 'required|exists:trading_pairs,id',
        ]);

        UserTradingPair::firstOrCreate([
            'user_id' => Auth::id(),
            'trading_pair_id' => request('trading_pair_id'),
        ]);

        return back();
    })->name('settings.pairs.store');

    Route::delete('/settings/pairs/{userTradingPair}', function (UserTradingPair $userTradingPair) {
        if (Auth::user()->is_admin) abort(403, 'Unauthorized');

        if ($userTradingPair->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $userTradingPair->delete();

        return back();
    })->name('settings.pairs.destroy');

    Route::post('/sync-luno', function () {
        if (Auth::user()->is_admin) abort(403, 'Admin cannot sync portfolio.');

        $luno = new \App\Services\LunoService();
        $luno->syncPortfolio();

        return redirect()->route('dashboard');
    })->middleware('auth')->name('sync.luno');
});
