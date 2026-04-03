<?php

namespace App\Http\Middleware;

class AuthenticateFilament extends \Filament\Http\Middleware\Authenticate
{
    protected function redirectTo($request): ?string
    {
        return route('login');
    }
}
