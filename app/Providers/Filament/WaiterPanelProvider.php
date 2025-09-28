<?php

namespace App\Providers\Filament;

use App\Http\Middleware\FilamentAuthenticate;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class WaiterPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('waiter')
            ->path('waiter')
            ->middleware([
                FilamentAuthenticate::class,
            ])
            ->colors([
                'primary' => Color::Green,
                'gray' => Color::Slate,
            ])
            ->favicon(asset('images/3.png'))
            ->brandLogo(asset('images/logo.png'))
            ->discoverResources(in: app_path('Filament/Waiter/Resources'), for: 'App\\Filament\\Waiter\\Resources')
            ->discoverPages(in: app_path('Filament/Waiter/Pages'), for: 'App\\Filament\\Waiter\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Waiter/Widgets'), for: 'App\\Filament\\Waiter\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
                Authenticate::class,
                \App\Http\Middleware\CheckWaiterAccess::class,
            ])
            ->brandName('Kitchen Dashboard')
            ->brandLogo(asset('images/logo.png'))
            ->darkMode(false);
    }
}