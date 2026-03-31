<?php

namespace Tests\Unit;

use App\Http\Middleware\RequireRole;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RequireRoleMiddlewareTest extends TestCase
{
    public function test_allows_active_user_with_matching_role(): void
    {
        $middleware = new RequireRole();
        $request = Request::create('/admin', 'GET');
        $request->setUserResolver(fn () => new User([
            'role' => 'admin',
            'status' => 'active',
        ]));

        $response = $middleware->handle($request, fn () => response('ok', 200), 'admin');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_blocks_user_with_non_matching_role(): void
    {
        $middleware = new RequireRole();
        $request = Request::create('/admin', 'GET');
        $request->setUserResolver(fn () => new User([
            'role' => 'aics_staff',
            'status' => 'active',
        ]));

        $response = $middleware->handle($request, fn () => response('ok', 200), 'admin');

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_blocks_inactive_user_even_with_matching_role(): void
    {
        $middleware = new RequireRole();
        $request = Request::create('/admin', 'GET');
        $request->setUserResolver(fn () => new User([
            'role' => 'admin',
            'status' => 'inactive',
        ]));

        $response = $middleware->handle($request, fn () => response('ok', 200), 'admin');

        $this->assertSame(403, $response->getStatusCode());
    }
}
