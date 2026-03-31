<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifySupabaseToken
{
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

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], 401);
    }
}
