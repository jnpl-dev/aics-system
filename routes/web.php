<?php

use App\Http\Controllers\AuthIntegrationController;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\OtpChallenge;
use Filament\Http\Middleware\SetUpPanel;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->get('/login', Login::class)
    ->middleware(SetUpPanel::class . ':admin')
    ->name('login');

Route::middleware('guest')->group(function (): void {
    Route::get('/otp', OtpChallenge::class)
        ->middleware(SetUpPanel::class . ':admin')
        ->name('filament.auth.otp');
});

Route::get('/dashboard', [AuthIntegrationController::class, 'dashboard'])->name('dashboard');
Route::post('/auth/login-attempt', [AuthIntegrationController::class, 'logLoginAttempt'])->name('auth.login-attempt');
Route::post('/auth/login-cooldown-check', [AuthIntegrationController::class, 'checkLoginCooldown'])->name('auth.login-cooldown-check');

Route::middleware(['supabase.auth'])->group(function (): void {
    Route::get('/auth/logout', [AuthIntegrationController::class, 'logout'])->name('auth.logout');
    Route::post('/auth/otp/request', [AuthIntegrationController::class, 'requestOtp'])->name('auth.otp.request');
    Route::post('/auth/otp/verify', [AuthIntegrationController::class, 'verifyOtp'])->name('auth.otp.verify');
    Route::get('/auth/session', [AuthIntegrationController::class, 'session'])->name('auth.session');
});

Route::middleware(['supabase.auth', 'role:admin'])->group(function (): void {
    Route::get('/admin/ping', [AuthIntegrationController::class, 'adminPing'])->name('admin.ping');
});

Route::middleware(['supabase.auth', 'role:admin'])->group(function (): void {
    Route::get('/dashboard/content/{tab}', [AuthIntegrationController::class, 'dashboardContent'])
        ->where('tab', '[a-z\-]+')
        ->name('dashboard.content');

    Route::post('/admin/users', [AuthIntegrationController::class, 'storeUser'])
        ->name('admin.users.store');
});
