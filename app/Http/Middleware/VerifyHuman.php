<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyHuman
{
    public function handle(Request $request, Closure $next): Response
    {
        $honeypot = $request->input('hp_token');

        if ($honeypot !== null && $honeypot !== '') {
            abort(Response::HTTP_FORBIDDEN, 'Access denied');
        }

        return $next($request);
    }
}
