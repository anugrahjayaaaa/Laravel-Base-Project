<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

// Health check (no auth)
Route::get('/up', fn () => response()->json(['status' => 'ok']));

// Auth
Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'show'])->name('login');
    Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'store'])
        ->middleware('throttle:10,15')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Stub routes for sidebar links (filled in by later phases: users/roles/permissions/audit/profile/sessions/api-tokens)
    Route::get('/users', fn () => view('placeholder', ['title' => 'Users']))->name('users.index')->middleware('can:user.view');
    Route::resource('roles', RoleController::class)->middleware('can:role.view');
    Route::resource('permissions', PermissionController::class)->middleware('can:permission.view');
    Route::get('/audit', [App\Http\Controllers\AuditController::class, 'index'])->name('audit.index')->middleware('can:audit.view');
    Route::get('/profile', fn () => view('placeholder', ['title' => 'Profile']))->name('profile.show');
    Route::get('/sessions', fn () => view('placeholder', ['title' => 'Sessions']))->name('sessions.index')->middleware('can:session.view');
    Route::get('/api-tokens', fn () => view('placeholder', ['title' => 'API Tokens']))->name('api-tokens.index')->middleware('can:api-token.view');

    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'destroy'])->name('logout');
});

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : view('auth.login'));
