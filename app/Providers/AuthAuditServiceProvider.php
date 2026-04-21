<?php

namespace App\Providers;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AuthAuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            $this->recordAuthEvent('AUTH_LOGIN_SUCCESS', 'login', $event->user);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            $this->recordAuthEvent('AUTH_LOGOUT', 'logout', $event->user);
        });
    }

    private function recordAuthEvent(string $eventCode, string $action, mixed $user): void
    {
        try {
            if (! Schema::hasTable('audit_log')) {
                return;
            }

            $email = is_object($user) && isset($user->email) ? (string) $user->email : '';
            $userId = is_object($user) && isset($user->user_id) && is_numeric($user->user_id)
                ? (int) $user->user_id
                : 0;

            $description = "event={$eventCode}";

            if ($email !== '') {
                $description .= "; email={$email}";
            }

            AuditLog::query()->create([
                'user_id' => $userId,
                'module' => 'authentication',
                'action' => $action,
                'description' => $description,
                'ip_address' => request()->ip(),
                'timestamp' => now('Asia/Manila'),
            ]);
        } catch (\Throwable) {
            // Non-fatal: auth logging should never block authentication flow.
        }
    }
}
