<?php

namespace App\Providers;

use App\Filament\Auth\Responses\LogoutResponse as FilamentLogoutResponse;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, FilamentLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->isServeCommand()) {
            return;
        }

        $defaultConnection = (string) config('database.default');
        $databaseName = (string) config("database.connections.{$defaultConnection}.database");

        try {
            DB::connection()->getPdo();

            fwrite(
                STDOUT,
                PHP_EOL."[AICS] ✅ Database connected successfully ({$defaultConnection}:{$databaseName}).".PHP_EOL
            );
        } catch (Throwable $exception) {
            fwrite(
                STDOUT,
                PHP_EOL."[AICS] ❌ Database connection failed ({$defaultConnection}:{$databaseName}).".PHP_EOL
            );
        }
    }

    /**
     * Detect when the app is booting for the `php artisan serve` command.
     */
    private function isServeCommand(): bool
    {
        if (! $this->app->runningInConsole()) {
            return false;
        }

        $argv = $_SERVER['argv'] ?? [];

        return in_array('serve', $argv, true);
    }
}
