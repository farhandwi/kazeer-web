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

class KitchenPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('kitchen')
            ->path('kitchen')
            ->middleware([
                FilamentAuthenticate::class,
            ])
            ->colors([
                'primary' => Color::Green,
                'gray' => Color::Slate,
            ])
            ->favicon(asset('images/3.png'))
            ->brandLogo(asset('images/logo.png'))
            ->discoverResources(in: app_path('Filament/Kitchen/Resources'), for: 'App\\Filament\\Kitchen\\Resources')
            ->discoverPages(in: app_path('Filament/Kitchen/Pages'), for: 'App\\Filament\\Kitchen\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Kitchen/Widgets'), for: 'App\\Filament\\Kitchen\\Widgets')
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
                \App\Http\Middleware\CheckKitchenAccess::class,
            ])
            ->brandName('Kitchen Dashboard')
            ->brandLogo(asset('images/logo.png'))
            ->darkMode(false);
    }
}