<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\LunoService;
use App\Http\Controllers\DashboardController;
use App\Models\TradingPair;



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


    // ==========================================
    //  USER ROUTES
    // ==========================================
    Route::get('/market', function () {
        return Inertia::render('Market');
    })->name('market');

    Route::get('/settings', function () {
        return Inertia::render('Settings');
    })->name('settings');

    Route::post('/sync-luno', function () {
        $luno = new \App\Services\LunoService();
        $luno->syncPortfolio();

        return redirect()->route('dashboard');
    })->middleware('auth')->name('sync.luno');
});
