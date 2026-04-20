<?php

namespace App\Providers\Filament;

use App\Filament\AicsStaff\Pages\Analytics;
use App\Filament\AicsStaff\Pages\Dashboard;
use App\Filament\Pages\Auth\Login;
use App\Http\Middleware\AuthenticateFilament;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AicsStaffPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $logoPath = file_exists(public_path('logo.png')) ? asset('logo.png') : null;

        return $panel
            ->id('aics-staff')
            ->path('aics-staff')
            ->spa(true, true)
            ->login(Login::class)
            ->brandName('')
            ->brandLogo($logoPath)
            ->darkModeBrandLogo(fn () => $logoPath)
            ->viteTheme('resources/css/filament/aics-staff/theme.css')
            ->colors([
                'primary' => Color::hex('#176334'),
                'success' => Color::hex('#6C9C02'),
                'gray' => Color::hex('#FFFDFF'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->pages([
                Dashboard::class,
                Analytics::class,
            ])
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                AuthenticateFilament::class,
            ]);
    }
}
