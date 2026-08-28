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

    // Password reset (guest only). Token state lives in DB table `password_reset_tokens`.
    Route::get('forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'update'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Stub routes for sidebar links (filled in by later phases: users/roles/permissions/audit/profile/sessions/api-tokens)
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index')->middleware('can:user.view');
    Route::resource('users', App\Http\Controllers\UserController::class)
        ->except(['show'])
        ->middleware('can:user.view');
    Route::post('/users/{user}/restore', [App\Http\Controllers\UserController::class, 'restore'])->name('users.restore')->middleware('can:user.restore');
    Route::post('/users/{user}/force-delete', [App\Http\Controllers\UserController::class, 'forceDelete'])->name('users.forceDelete')->middleware('can:user.force-delete');
    Route::post('/users/{user}/unlock', [App\Http\Controllers\UserController::class, 'unlock'])->name('users.unlock')->middleware('can:user.edit');
    Route::post('/users/{user}/reset-link', [App\Http\Controllers\UserController::class, 'sendResetLink'])->name('users.reset-link')->middleware('can:user.edit');

    Route::resource('roles', RoleController::class)->middleware('can:role.view');
    Route::post('/roles/{role}/restore', [RoleController::class, 'restore'])->name('roles.restore')->middleware('can:role.restore');
    Route::post('/roles/{role}/force-delete', [RoleController::class, 'forceDelete'])->name('roles.forceDelete')->middleware('can:role.force-delete');

    Route::resource('permissions', PermissionController::class)->middleware('can:permission.view');
    Route::post('/permissions/{permission}/restore', [PermissionController::class, 'restore'])->name('permissions.restore')->middleware('can:permission.restore');
    Route::post('/permissions/{permission}/force-delete', [PermissionController::class, 'forceDelete'])->name('permissions.forceDelete')->middleware('can:permission.force-delete');

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [App\Http\Controllers\ProfileController::class, 'changePassword'])->name('profile.password');

    Route::get('/sessions', [App\Http\Controllers\SessionController::class, 'index'])->name('sessions.index')->middleware('can:session.view');
    Route::post('/sessions/logout-others', [App\Http\Controllers\SessionController::class, 'logoutOthers'])->name('sessions.logoutOthers')->middleware('can:session.revoke');

    Route::get('/audit', [App\Http\Controllers\AuditController::class, 'index'])->name('audit.index')->middleware('can:audit.view');
    Route::get('/api-tokens', [App\Http\Controllers\ApiTokenController::class, 'index'])->name('api-tokens.index')->middleware('can:api-token.view');
    Route::resource('api-tokens', App\Http\Controllers\ApiTokenController::class)
        ->only(['store', 'destroy'])
        ->middleware('can:api-token.create');

    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'destroy'])->name('logout');
});

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : view('auth.login'));
