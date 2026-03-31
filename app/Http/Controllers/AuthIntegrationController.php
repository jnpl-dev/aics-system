<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AuthIntegrationController extends Controller
{
    private const OTP_SESSION_TTL_MINUTES = 10;

    private const OTP_VERIFIED_TTL_HOURS = 8;

    private const OTP_MAX_ATTEMPTS = 5;

    private const DASHBOARD_TABS = [
        'dashboard' => [
            'title' => 'Dashboard',
            'view' => 'admin.tabs.dashboard',
        ],
        'user-management' => [
            'title' => 'User Management',
            'view' => 'admin.tabs.user-management',
        ],
        'audit-log' => [
            'title' => 'Audit Log',
            'view' => 'admin.tabs.audit-log',
        ],
        'system-activity' => [
            'title' => 'System Activity',
            'view' => 'admin.tabs.system-activity',
        ],
        'sms-settings' => [
            'title' => 'SMS Settings',
            'view' => 'admin.tabs.sms-settings',
        ],
        'system-settings' => [
            'title' => 'System Settings',
            'view' => 'admin.tabs.system-settings',
        ],
        'account-settings' => [
            'title' => 'Account Settings',
            'view' => 'admin.tabs.account-settings',
        ],
    ];

    public function showLogin(): View
    {
        return view('auth.login', [
            'supabaseUrl' => config('supabase.url'),
            'supabaseAnonKey' => config('supabase.anon_key'),
        ]);
    }

    public function session(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Supabase token validated.',
            'user' => [
                'user_id' => $user?->user_id,
                'email' => $user?->email,
                'first_name' => $user?->first_name,
                'last_name' => $user?->last_name,
                'role' => $user?->role,
                'status' => $user?->status,
            ],
            'supabase_user' => $request->attributes->get('supabase_user'),
        ]);
    }

    public function requestOtp(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokenHash = $this->tokenHash($request->bearerToken());

        if (!is_string($user?->email) || $user->email === '') {
            return response()->json([
                'message' => 'Unable to request OTP because user email is missing.',
            ], 422);
        }

        $otpCode = (string) random_int(100000, 999999);
        $otpSessionId = (string) Str::uuid();

        Cache::put(
            $this->otpSessionCacheKey($otpSessionId),
            [
                'email' => $user->email,
                'user_id' => $user->user_id,
                'token_hash' => $tokenHash,
                'code_hash' => Hash::make($otpCode),
                'attempts' => 0,
                'max_attempts' => self::OTP_MAX_ATTEMPTS,
            ],
            now()->addMinutes(self::OTP_SESSION_TTL_MINUTES)
        );

        Cache::forget($this->otpVerifiedCacheKey($tokenHash));

        try {
            Mail::raw(
                "Your AICS verification code is {$otpCode}. This code expires in 10 minutes.",
                static function ($message) use ($user): void {
                    $message
                        ->to($user->email)
                        ->subject('AICS Login Verification Code');
                }
            );
        } catch (Throwable $exception) {
            Cache::forget($this->otpSessionCacheKey($otpSessionId));

            return response()->json([
                'message' => 'OTP could not be sent to your email. Please try again.',
            ], 500);
        }

        return response()->json([
            'message' => 'OTP sent successfully. Please check your email for the 6-digit code.',
            'otp_session_id' => $otpSessionId,
            'expires_in_seconds' => self::OTP_SESSION_TTL_MINUTES * 60,
            'masked_email' => $this->maskEmail($user->email),
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'otp_session_id' => ['required', 'uuid'],
            'otp_code' => ['required', 'digits:6'],
        ]);

        $cacheKey = $this->otpSessionCacheKey($validated['otp_session_id']);
        $otpSession = Cache::get($cacheKey);

        if (!is_array($otpSession)) {
            return response()->json([
                'message' => 'OTP session expired or invalid. Please request a new code.',
            ], 422);
        }

        $tokenHash = $this->tokenHash($request->bearerToken());
        if (($otpSession['token_hash'] ?? null) !== $tokenHash) {
            return response()->json([
                'message' => 'OTP session does not match the active login session.',
            ], 403);
        }

        $attempts = (int) ($otpSession['attempts'] ?? 0);
        $maxAttempts = (int) ($otpSession['max_attempts'] ?? self::OTP_MAX_ATTEMPTS);

        if ($attempts >= $maxAttempts) {
            Cache::forget($cacheKey);

            return response()->json([
                'message' => 'Maximum OTP attempts exceeded. Please request a new code.',
            ], 429);
        }

        $isValidCode = Hash::check((string) $validated['otp_code'], (string) ($otpSession['code_hash'] ?? ''));

        if (!$isValidCode) {
            $otpSession['attempts'] = $attempts + 1;
            Cache::put($cacheKey, $otpSession, now()->addMinutes(self::OTP_SESSION_TTL_MINUTES));

            return response()->json([
                'message' => 'Invalid OTP code. Please try again.',
                'attempts_remaining' => max($maxAttempts - (int) $otpSession['attempts'], 0),
            ], 422);
        }

        Cache::put(
            $this->otpVerifiedCacheKey($tokenHash),
            [
                'email' => $otpSession['email'] ?? null,
                'verified_at' => now()->toIso8601String(),
            ],
            now()->addHours(self::OTP_VERIFIED_TTL_HOURS)
        );

        Cache::forget($cacheKey);

        return response()->json([
            'message' => 'OTP verified successfully. Access granted.',
        ]);
    }

    public function dashboard(Request $request): View
    {
        $requestedTab = (string) $request->query('tab', 'dashboard');
        $tabKey = array_key_exists($requestedTab, self::DASHBOARD_TABS) ? $requestedTab : 'dashboard';
        $tabConfig = self::DASHBOARD_TABS[$tabKey];

        return view('admin.dashboard', [
            'activeTab' => $tabKey,
            'pageTitle' => $tabConfig['title'],
            'initialTabView' => $tabConfig['view'],
            'sidebarRole' => (string) $request->query('role', 'admin'),
            'sidebarFullName' => (string) $request->query('name', 'Loading user...'),
        ]);
    }

    public function dashboardContent(string $tab): View
    {
        if (!array_key_exists($tab, self::DASHBOARD_TABS)) {
            abort(404);
        }

        return view(self::DASHBOARD_TABS[$tab]['view'], [
            'tabKey' => $tab,
            'pageTitle' => self::DASHBOARD_TABS[$tab]['title'],
        ]);
    }

    public function adminPing(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Admin route access granted.',
            'email' => $request->user()?->email,
            'role' => $request->user()?->role,
        ]);
    }

    public function logout(): JsonResponse
    {
        $tokenHash = $this->tokenHash(request()->bearerToken());
        if ($tokenHash !== '') {
            Cache::forget($this->otpVerifiedCacheKey($tokenHash));
        }

        return response()->json([
            'message' => 'Client should clear Supabase token locally.',
        ]);
    }

    private function tokenHash(?string $token): string
    {
        if (!is_string($token) || trim($token) === '') {
            return '';
        }

        return hash('sha256', $token);
    }

    private function otpSessionCacheKey(string $otpSessionId): string
    {
        return "auth:otp:session:{$otpSessionId}";
    }

    private function otpVerifiedCacheKey(string $tokenHash): string
    {
        return "auth:otp:verified:{$tokenHash}";
    }

    private function maskEmail(string $email): string
    {
        $segments = explode('@', $email);
        if (count($segments) !== 2) {
            return $email;
        }

        $localPart = $segments[0];
        $domain = $segments[1];

        if (strlen($localPart) <= 2) {
            return str_repeat('*', strlen($localPart)).'@'.$domain;
        }

        return substr($localPart, 0, 2).str_repeat('*', max(strlen($localPart) - 2, 1)).'@'.$domain;
    }
}
