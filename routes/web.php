<?php

use App\Http\Controllers\AuthIntegrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthIntegrationController::class, 'showLogin'])->name('login');
Route::get('/dashboard', [AuthIntegrationController::class, 'dashboard'])->name('dashboard');
Route::get('/auth/logout', [AuthIntegrationController::class, 'logout'])->name('auth.logout');

Route::middleware(['supabase.auth'])->group(function (): void {
    Route::get('/auth/session', [AuthIntegrationController::class, 'session'])->name('auth.session');
});

Route::middleware(['supabase.auth', 'role:admin'])->group(function (): void {
    Route::get('/admin/ping', [AuthIntegrationController::class, 'adminPing'])->name('admin.ping');
});

Route::middleware(['supabase.auth', 'role:admin,system_admin'])->group(function (): void {
    Route::get('/dashboard/content/{tab}', [AuthIntegrationController::class, 'dashboardContent'])
        ->where('tab', '[a-z\-]+')
        ->name('dashboard.content');
});
