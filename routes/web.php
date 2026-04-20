<?php

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\OtpChallenge;
use App\Http\Controllers\ApplicantApplicationController;
use App\Http\Controllers\ApplicantTrackingController;
use App\Http\Controllers\AuthIntegrationController;
use Filament\Http\Middleware\SetUpPanel;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/apply', [ApplicantApplicationController::class, 'create'])->name('applicant.apply');
Route::post('/apply', [ApplicantApplicationController::class, 'store'])->name('applicant.apply.store');
Route::get('/apply/success/{referenceCode}', [ApplicantApplicationController::class, 'success'])->name('applicant.apply.success');
Route::view('/address-demo', 'applicant.address-demo')->name('applicant.address-demo');

Route::get('/track', [ApplicantTrackingController::class, 'index'])->name('applicant.track');
Route::post('/track/access', [ApplicantTrackingController::class, 'access'])->name('applicant.track.access');
Route::get('/track/application', [ApplicantTrackingController::class, 'show'])->name('applicant.track.application');
Route::post('/track/application/resubmit', [ApplicantTrackingController::class, 'resubmit'])->name('applicant.track.resubmit');

Route::get('/staff-login', fn () => redirect()->to('/login'))->name('staff.login');

Route::middleware('guest')->get('/login', Login::class)
    ->middleware(SetUpPanel::class.':admin')
    ->name('login');

Route::middleware('guest')->get('/admin/login', fn () => redirect()->route('login'));
Route::middleware('guest')->get('/aics-staff/login', fn () => redirect()->route('login'));

Route::get('/otp', OtpChallenge::class)
    ->middleware(SetUpPanel::class.':admin')
    ->name('filament.auth.otp');

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
