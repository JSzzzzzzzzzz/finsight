<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use App\Services\LunoService;
use App\Http\Controllers\DashboardController;
use App\Models\TradingPair;
use App\Models\UserTradingPair;
use App\Models\ApiKey;
use App\Models\User;
use App\Services\NewsService;
use App\Services\OpenAIService;

Route::get('/test-news/{symbol}', function ($symbol) {
    $news = new NewsService();

    return response()->json(
        $news->getCryptoNews($symbol)
    );
});

Route::get('/test-news-raw', function () {
    return response()->json(
        Http::get('https://min-api.cryptocompare.com/data/v2/news/', [
            'lang' => 'EN',
            'api_key' => env('CRYPTOCOMPARE_API_KEY'),
        ])->json()
    );
});

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
    'user.status',
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

        return Inertia::render('Admin/AUsers', [
            'users' => User::where('is_admin', false)
                ->latest()
                ->get()
                ->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->is_admin ? 'Admin' : 'User',
                    'status' => ucfirst($user->status ?? 'active'),
                    'joined' => $user->created_at->diffForHumans(),
                ]),
        ]);
    })->name('admin.users');

    Route::patch('/admin/users/{user}/toggle-status', function (User $user) {
        if (!Auth::user()->is_admin) abort(403, 'Unauthorized');

        if ($user->is_admin) {
            abort(403, 'Admin account cannot be banned.');
        }

        $user->update([
            'status' => $user->status === 'banned' ? 'active' : 'banned',
        ]);

        return back();
    })->name('admin.users.toggle-status');

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

    Route::get('/market-summary', function () {
        if (Auth::user()->is_admin) abort(403, 'Unauthorized');

        $newsService = new NewsService();
        $openAI = new OpenAIService();

        $news = $newsService->getCryptoNews('BTC');

        $headlines = collect($news)
            ->pluck('title')
            ->take(10)
            ->values()
            ->toArray();

        $sentimentResponse = Http::post('http://127.0.0.1:5001/analyze-sentiment', [
            'texts' => $headlines,
        ]);

        $sentiment = $sentimentResponse->json();

        $summary = $openAI->generateMarketAbstract($headlines, $sentiment);

        return response()->json([
            'summary' => $summary,
            'sentiment' => $sentiment,
            'generated_at' => now('Asia/Kuala_Lumpur')->format('H:i'),
        ]);
    })->name('market.summary');

    Route::get('/market/scanner', function () {
        if (Auth::user()->is_admin) abort(403, 'Unauthorized');

        $luno = new LunoService();

        $selectedPairs = UserTradingPair::with('tradingPair')
            ->where('user_id', Auth::id())
            ->get();

        $tickersData = $luno->getAllTickers();

        $tickers = collect($tickersData['tickers'] ?? []);

        $marketData = $selectedPairs->map(function ($item) use ($tickers) {
            $pair = $item->tradingPair->symbol;

            $ticker = $tickers->firstWhere('pair', $pair);

            return [
                'id' => $item->id,
                'pair' => $pair,
                'display_pair' => $pair === 'XBTMYR' ? 'BTCMYR' : $pair,
                'symbol' => $pair === 'XBTMYR'
                    ? 'BTC'
                    : str_replace('MYR', '', $pair),
                'source' => $item->tradingPair->source,
                'price' => (float) ($ticker['last_trade'] ?? 0),
                'bid' => (float) ($ticker['bid'] ?? 0),
                'ask' => (float) ($ticker['ask'] ?? 0),
            ];
        });

        return response()->json($marketData);
    })->name('market.scanner');

    Route::post('/market/analyze', function () {
        if (Auth::user()->is_admin) {
            abort(403, 'Unauthorized');
        }

        request()->validate([
            'symbol' => 'required|string',
        ]);

        $symbol = strtoupper(request('symbol'));

        if ($symbol === 'XBT') {
            $symbol = 'BTC';
        }

        $newsService = new NewsService();

        $news = $newsService->getCryptoNews($symbol);

        $headlines = collect($news)
            ->pluck('title')
            ->filter()
            ->values()
            ->toArray();

        $sentimentResponse = Http::post(
            'http://127.0.0.1:5001/analyze-sentiment',
            [
                'texts' => $headlines
            ]
        );

        $sentimentData = $sentimentResponse->json();

        $openAI = new OpenAIService();

        $explanation = $openAI->generateAssetExplanation(
            $symbol,
            $news,
            $sentimentData
        );

        return response()->json([
            'symbol' => $symbol,
            'news' => $news,
            'sentiment' => $sentimentData,
            'explanation' => $explanation,
        ]);
    })->name('market.analyze');

    Route::get('/settings', function () {
        if (Auth::user()->is_admin) abort(403, 'Unauthorized');

        $selectedPairIds = UserTradingPair::where('user_id', Auth::id())
            ->pluck('trading_pair_id')
            ->toArray();
        $hasApiKey = ApiKey::where('user_id', Auth::id())
            ->where('exchange', 'Luno')
            ->exists();

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

            'hasApiKey' => $hasApiKey,
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

    Route::post('/settings/api-key', function () {
        if (Auth::user()->is_admin) abort(403, 'Unauthorized');

        request()->validate([
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
        ]);

        ApiKey::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'exchange' => 'Luno',
            ],
            [
                'api_key' => Crypt::encryptString(request('api_key')),
                'api_secret' => Crypt::encryptString(request('api_secret')),
            ]
        );

        return back();
    })->name('settings.api-key.store');

    Route::post('/sync-luno', function () {
        if (Auth::user()->is_admin) abort(403, 'Admin cannot sync portfolio.');

        try {
            $luno = new LunoService();
            $luno->syncPortfolio();

            return redirect()->route('dashboard')
                ->with('success', 'Portfolio synced successfully.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', $e->getMessage());
        }
    })->middleware('auth')->name('sync.luno');
});
