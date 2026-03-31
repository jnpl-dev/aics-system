<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->forbidden('Unauthenticated user.');
        }

        if (($user->status ?? null) !== 'active') {
            return $this->forbidden('Inactive user cannot access this resource.');
        }

        if ($roles !== [] && ! in_array($user->role, $roles, true)) {
            return $this->forbidden('Insufficient role permissions.');
        }

        return $next($request);
    }

    private function forbidden(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], 403);
    }
}
