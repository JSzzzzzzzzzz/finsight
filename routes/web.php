<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Services\LunoService;
use App\Http\Controllers\DashboardController;



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
        if (!auth()->user()->is_admin) abort(403, 'Unauthorized');
        return Inertia::render('Admin/ADashboard');
    })->name('admin.dashboard');

    // B. Manage Users
    Route::get('/admin/users', function () {
        if (!auth()->user()->is_admin) abort(403, 'Unauthorized');
        return Inertia::render('Admin/AUsers');
    })->name('admin.users');

    // C. Manage Pairs
    Route::get('/admin/pairs', function () {
        if (!auth()->user()->is_admin) abort(403, 'Unauthorized');
        return Inertia::render('Admin/APairs');
    })->name('admin.pairs');


    // ==========================================
    //  USER ROUTES
    // ==========================================
    Route::get('/market', function () {
        return Inertia::render('Market');
    })->name('market');

    Route::get('/settings', function () {
        return Inertia::render('Settings');
    })->name('settings');

    Route::get('/sync-luno', function () {
        $luno = new LunoService();

        $luno->syncPortfolio();

        return 'Luno fully synced';
    })->name('sync.luno');
});


