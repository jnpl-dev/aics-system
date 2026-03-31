<?php

namespace Tests\Unit;

use App\Http\Middleware\VerifySupabaseToken;
use Illuminate\Http\Request;
use Tests\TestCase;

class VerifySupabaseTokenMiddlewareTest extends TestCase
{
    public function test_returns_unauthorized_when_bearer_token_is_missing(): void
    {
        $middleware = new VerifySupabaseToken();
        $request = Request::create('/secure', 'GET');

        $response = $middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_returns_unauthorized_when_supabase_config_is_incomplete(): void
    {
        config()->set('supabase.url', '');
        config()->set('supabase.anon_key', '');

        $middleware = new VerifySupabaseToken();
        $request = Request::create('/secure', 'GET');
        $request->headers->set('Authorization', 'Bearer token-value');

        $response = $middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(401, $response->getStatusCode());
    }
}
