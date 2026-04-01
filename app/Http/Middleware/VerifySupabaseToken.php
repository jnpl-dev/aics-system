<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class VerifySupabaseToken
{
    private const OTP_BYPASS_ROUTES = [
        'auth.otp.request',
        'auth.otp.verify',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (! $bearerToken) {
            return $this->unauthorized('Missing bearer token.');
        }

        $supabaseUrl = rtrim((string) config('supabase.url', ''), '/');
        $supabaseAnonKey = (string) config('supabase.anon_key', '');
        $userEndpoint = (string) config('supabase.auth_user_endpoint', '/auth/v1/user');

        if ($supabaseUrl === '' || $supabaseAnonKey === '') {
            return $this->unauthorized('Supabase auth configuration is incomplete.');
        }

        $response = Http::timeout(8)
            ->withHeaders([
                'apikey' => $supabaseAnonKey,
                'Authorization' => 'Bearer '.$bearerToken,
            ])
            ->acceptJson()
            ->get($supabaseUrl.$userEndpoint);

        if (! $response->successful()) {
            if (Schema::hasTable('audit_log')) {
                try {
                    AuditLog::query()->create([
                        'user_id' => 0,
                        'module' => 'authentication',
                        'action' => 'login',
                        'description' => 'event=AUTH_SESSION_EXPIRED; reason=invalid_or_expired_supabase_token',
                        'ip_address' => $request->ip(),
                        'timestamp' => now(),
                    ]);
                } catch (Throwable) {
                    // Non-fatal: some environments enforce FK on audit_log.user_id and reject anonymous user_id=0.
                }
            }

            return $this->unauthorized('Invalid or expired Supabase token.');
        }

        $supabaseUser = $response->json();
        $email = is_array($supabaseUser) ? ($supabaseUser['email'] ?? null) : null;

        if (! is_string($email) || $email === '') {
            return $this->unauthorized('Supabase user payload is missing email.');
        }

        /** @var User|null $localUser */
        $localUser = User::query()->where('email', $email)->first();

        if (! $localUser) {
            return $this->unauthorized('No matching local account for Supabase user.');
        }

        if (($localUser->status ?? null) !== 'active') {
            return $this->unauthorized('Local account is inactive.');
        }

        Auth::setUser($localUser);
        $request->setUserResolver(static fn (): User => $localUser);
        $request->attributes->set('supabase_user', $supabaseUser);

        if ($this->requiresOtpVerification($request) && !Cache::has($this->otpVerifiedCacheKey($bearerToken))) {
            return response()->json([
                'message' => 'OTP verification required before accessing protected resources.',
            ], 403);
        }

        return $next($request);
    }

    private function requiresOtpVerification(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        return !in_array($routeName, self::OTP_BYPASS_ROUTES, true);
    }

    private function otpVerifiedCacheKey(string $token): string
    {
        return 'auth:otp:verified:'.hash('sha256', $token);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], 401);
    }
}
