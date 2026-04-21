<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AuthIntegrationController extends Controller
{
    private const OTP_SESSION_TTL_MINUTES = 10;

    private const OTP_VERIFIED_TTL_HOURS = 8;

    private const OTP_MAX_ATTEMPTS = 5;

    private const EVENT_AUTH_LOGIN_SUCCESS = 'AUTH_LOGIN_SUCCESS';

    private const EVENT_AUTH_LOGIN_FAILED = 'AUTH_LOGIN_FAILED';

    private const EVENT_AUTH_LOGOUT = 'AUTH_LOGOUT';

    private const EVENT_AUTH_SESSION_EXPIRED = 'AUTH_SESSION_EXPIRED';

    private const EVENT_OTP_GENERATED_SENT = 'OTP_GENERATED_SENT';

    private const EVENT_OTP_RESEND = 'OTP_RESEND';

    private const EVENT_OTP_VERIFIED = 'OTP_VERIFIED';

    private const EVENT_OTP_FAILED = 'OTP_FAILED';

    private const EVENT_OTP_EXPIRED = 'OTP_EXPIRED';

    private const LOGIN_FAILED_MAX_ATTEMPTS = 5;

    private const LOGIN_FAILED_COOLDOWN_MINUTES = 15;

    public function session(Request $request): JsonResponse
    {
        $user = $request->user();
        $normalizedRole = $this->normalizeRole((string) ($user?->role ?? ''));

        return response()->json([
            'message' => 'Supabase token validated.',
            'user' => [
                'user_id' => $user?->user_id,
                'email' => $user?->email,
                'first_name' => $user?->first_name,
                'last_name' => $user?->last_name,
                'role' => $normalizedRole,
                'status' => $user?->status,
            ],
            'supabase_user' => $request->attributes->get('supabase_user'),
        ]);
    }

    public function logLoginAttempt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'outcome' => ['required', 'in:success,failed,session_expired'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $email = strtolower((string) $validated['email']);
        $user = \App\Models\User::query()->where('email', $email)->first();
        $outcome = (string) $validated['outcome'];
        $ipAddress = (string) ($request->ip() ?? 'unknown');

        if ($outcome === 'success') {
            $this->clearLoginAttemptState($email, $ipAddress);
        }

        if ($outcome === 'failed') {
            $cooldown = $this->getCooldownSeconds($email, $ipAddress);

            if ($cooldown > 0) {
                $this->recordAuditEvent(
                    $request,
                    self::EVENT_AUTH_LOGIN_FAILED,
                    is_int($user?->user_id) ? $user->user_id : 0,
                    $email,
                    [
                        'reason' => 'cooldown_active',
                        'source' => 'frontend',
                        'cooldown_seconds' => $cooldown,
                    ]
                );

                return response()->json([
                    'message' => "Too many failed attempts. Try again in {$cooldown} seconds.",
                    'cooldown_active' => true,
                    'retry_after_seconds' => $cooldown,
                ], 429);
            }
        }

        $eventCode = match ($outcome) {
            'success' => self::EVENT_AUTH_LOGIN_SUCCESS,
            'session_expired' => self::EVENT_AUTH_SESSION_EXPIRED,
            default => self::EVENT_AUTH_LOGIN_FAILED,
        };

        $this->recordAuditEvent(
            $request,
            $eventCode,
            is_int($user?->user_id) ? $user->user_id : 0,
            $email,
            [
                'reason' => (string) ($validated['reason'] ?? ''),
                'source' => 'frontend',
            ]
        );

        if ($outcome === 'failed') {
            $attempts = $this->incrementFailedLoginAttempts($email, $ipAddress);

            if ($attempts >= self::LOGIN_FAILED_MAX_ATTEMPTS) {
                $this->startLoginCooldown($email, $ipAddress);

                return response()->json([
                    'message' => 'Too many failed attempts. Login is locked for 15 minutes.',
                    'cooldown_active' => true,
                    'retry_after_seconds' => self::LOGIN_FAILED_COOLDOWN_MINUTES * 60,
                ], 429);
            }

            return response()->json([
                'message' => 'Login attempt logged.',
                'attempts_remaining' => max(self::LOGIN_FAILED_MAX_ATTEMPTS - $attempts, 0),
            ]);
        }

        return response()->json([
            'message' => 'Login attempt logged.',
        ]);
    }

    public function checkLoginCooldown(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower((string) $validated['email']);
        $ipAddress = (string) ($request->ip() ?? 'unknown');
        $cooldown = $this->getCooldownSeconds($email, $ipAddress);

        if ($cooldown > 0) {
            return response()->json([
                'message' => "Too many failed attempts. Please wait {$cooldown} seconds before trying again.",
                'cooldown_active' => true,
                'retry_after_seconds' => $cooldown,
            ], 429);
        }

        return response()->json([
            'message' => 'Login allowed.',
            'cooldown_active' => false,
        ]);
    }

    public function requestOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'is_resend' => ['nullable', 'boolean'],
        ]);

        $isResend = (bool) ($validated['is_resend'] ?? false);
        $user = $request->user();
        $tokenHash = $this->tokenHash($request->bearerToken());
        $email = is_string($user?->email) ? $user->email : null;
        $userId = is_int($user?->user_id) ? $user->user_id : 0;

        if (!is_string($user?->email) || $user->email === '') {
            $this->recordAuditEvent($request, self::EVENT_OTP_FAILED, $userId, $email, [
                'reason' => 'missing_email',
                'is_resend' => $isResend,
            ]);

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
        } catch (Throwable) {
            Cache::forget($this->otpSessionCacheKey($otpSessionId));

            $this->recordAuditEvent($request, self::EVENT_OTP_FAILED, $userId, $user->email, [
                'reason' => 'mail_delivery_failed',
                'otp_session_id' => $otpSessionId,
                'is_resend' => $isResend,
            ]);

            return response()->json([
                'message' => 'OTP could not be sent to your email. Please try again.',
            ], 500);
        }

        $this->recordAuditEvent(
            $request,
            $isResend ? self::EVENT_OTP_RESEND : self::EVENT_OTP_GENERATED_SENT,
            $userId,
            $user->email,
            [
            'otp_session_id' => $otpSessionId,
            ]
        );

        if (!$isResend) {
            $this->recordAuditEvent($request, self::EVENT_AUTH_LOGIN_SUCCESS, $userId, $user->email, [
                'otp_session_id' => $otpSessionId,
            ]);
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
        $user = $request->user();
        $email = is_string($user?->email) ? $user->email : null;
        $userId = is_int($user?->user_id) ? $user->user_id : 0;

        $validated = $request->validate([
            'otp_session_id' => ['required', 'uuid'],
            'otp_code' => ['required', 'digits:6'],
        ]);

        $cacheKey = $this->otpSessionCacheKey($validated['otp_session_id']);
        $otpSession = Cache::get($cacheKey);

        if (!is_array($otpSession)) {
            $this->recordAuditEvent($request, self::EVENT_OTP_EXPIRED, $userId, $email, [
                'reason' => 'otp_session_invalid_or_expired',
                'otp_session_id' => $validated['otp_session_id'],
            ]);

            return response()->json([
                'message' => 'OTP session expired or invalid. Please request a new code.',
            ], 422);
        }

        $tokenHash = $this->tokenHash($request->bearerToken());
        if (($otpSession['token_hash'] ?? null) !== $tokenHash) {
            $this->recordAuditEvent($request, self::EVENT_OTP_FAILED, $userId, $email, [
                'reason' => 'otp_session_token_mismatch',
                'otp_session_id' => $validated['otp_session_id'],
            ]);

            return response()->json([
                'message' => 'OTP session does not match the active login session.',
            ], 403);
        }

        $attempts = (int) ($otpSession['attempts'] ?? 0);
        $maxAttempts = (int) ($otpSession['max_attempts'] ?? self::OTP_MAX_ATTEMPTS);

        if ($attempts >= $maxAttempts) {
            Cache::forget($cacheKey);

            $this->recordAuditEvent($request, self::EVENT_OTP_FAILED, $userId, $email, [
                'reason' => 'otp_max_attempts_exceeded',
                'otp_session_id' => $validated['otp_session_id'],
                'attempts' => $attempts,
                'max_attempts' => $maxAttempts,
            ]);

            return response()->json([
                'message' => 'Maximum OTP attempts exceeded. Please request a new code.',
            ], 429);
        }

        $isValidCode = Hash::check((string) $validated['otp_code'], (string) ($otpSession['code_hash'] ?? ''));

        if (!$isValidCode) {
            $otpSession['attempts'] = $attempts + 1;
            Cache::put($cacheKey, $otpSession, now()->addMinutes(self::OTP_SESSION_TTL_MINUTES));

            $this->recordAuditEvent($request, self::EVENT_OTP_FAILED, $userId, $email, [
                'reason' => 'otp_code_invalid',
                'otp_session_id' => $validated['otp_session_id'],
                'attempts' => (int) $otpSession['attempts'],
                'attempts_remaining' => max($maxAttempts - (int) $otpSession['attempts'], 0),
            ]);

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

        $this->recordAuditEvent($request, self::EVENT_OTP_VERIFIED, $userId, $email, [
            'otp_session_id' => $validated['otp_session_id'],
        ]);

        return response()->json([
            'message' => 'OTP verified successfully. Access granted.',
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
        $user = request()->user();

        $email = is_string($user?->email) ? $user->email : null;
        $userId = is_int($user?->user_id) ? $user->user_id : 0;

        if ($tokenHash !== '') {
            Cache::forget($this->otpVerifiedCacheKey($tokenHash));
        }

        $this->recordAuditEvent(request(), self::EVENT_AUTH_LOGOUT, $userId, $email);

        return response()->json([
            'message' => 'Client should clear Supabase token locally.',
        ]);
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function recordAuditEvent(
        Request $request,
        string $eventCode,
        int $userId,
        ?string $email = null,
        array $metadata = []
    ): void {
        [$module, $action] = $this->mapAuditModuleAndAction($eventCode);

        $descriptionParts = [
            "event={$eventCode}",
        ];

        if (is_string($email) && $email !== '') {
            $descriptionParts[] = "email={$email}";
        }

        if ($metadata !== []) {
            $descriptionParts[] = 'meta='.json_encode($metadata, JSON_UNESCAPED_UNICODE);
        }

        AuditLog::query()->create([
            'user_id' => $userId,
            'module' => $module,
            'action' => $action,
            'description' => implode('; ', $descriptionParts),
            'ip_address' => $request->ip(),
            'timestamp' => now('Asia/Manila'),
        ]);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function mapAuditModuleAndAction(string $eventCode): array
    {
        return match ($eventCode) {
            self::EVENT_AUTH_LOGOUT => ['authentication', 'logout'],
            self::EVENT_OTP_GENERATED_SENT,
            self::EVENT_OTP_RESEND,
            self::EVENT_OTP_VERIFIED,
            self::EVENT_OTP_FAILED,
            self::EVENT_OTP_EXPIRED => ['otp', 'configure'],
            default => ['authentication', 'login'],
        };
    }

    private function normalizeRole(string $role): string
    {
        return $role === 'system_admin' ? 'admin' : $role;
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

    private function loginFailedAttemptsCacheKey(string $email, string $ipAddress): string
    {
        return 'auth:login:failed_attempts:'.md5($email.'|'.$ipAddress);
    }

    private function loginCooldownCacheKey(string $email, string $ipAddress): string
    {
        return 'auth:login:cooldown_until:'.md5($email.'|'.$ipAddress);
    }

    private function incrementFailedLoginAttempts(string $email, string $ipAddress): int
    {
        $attemptsKey = $this->loginFailedAttemptsCacheKey($email, $ipAddress);
        $attempts = ((int) Cache::get($attemptsKey, 0)) + 1;

        Cache::put(
            $attemptsKey,
            $attempts,
            now()->addMinutes(self::LOGIN_FAILED_COOLDOWN_MINUTES)
        );

        return $attempts;
    }

    private function startLoginCooldown(string $email, string $ipAddress): void
    {
        $cooldownUntil = now()->addMinutes(self::LOGIN_FAILED_COOLDOWN_MINUTES);

        Cache::put(
            $this->loginCooldownCacheKey($email, $ipAddress),
            $cooldownUntil->timestamp,
            $cooldownUntil
        );

        Cache::forget($this->loginFailedAttemptsCacheKey($email, $ipAddress));
    }

    private function clearLoginAttemptState(string $email, string $ipAddress): void
    {
        Cache::forget($this->loginFailedAttemptsCacheKey($email, $ipAddress));
        Cache::forget($this->loginCooldownCacheKey($email, $ipAddress));
    }

    private function getCooldownSeconds(string $email, string $ipAddress): int
    {
        $cooldownUntil = (int) Cache::get($this->loginCooldownCacheKey($email, $ipAddress), 0);
        if ($cooldownUntil <= 0) {
            return 0;
        }

        $remaining = $cooldownUntil - now()->timestamp;

        if ($remaining <= 0) {
            Cache::forget($this->loginCooldownCacheKey($email, $ipAddress));
            return 0;
        }

        return $remaining;
    }
}
