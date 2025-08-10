<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Outerweb\FilamentTranslatableFields\Filament\Plugins\FilamentTranslatableFieldsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->defaultThemeMode(ThemeMode::Light)
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('56px')
            ->favicon(asset('favicon.ico'))
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors(colors())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->navigationItems([
                NavigationItem::make('telescope')
                    ->label('Telescope')
                    ->badge(fn(): string => '●')
                    ->badgeTooltip('Telescope helps track what happens behind the scenes in your app.')
                    ->url(fn(): string => app()->environment('local') ? route('telescope') : '#', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-chart-bar-square')
                    ->group('Monitor')
                    ->visible(fn(): bool => !app()->environment('testing') && app()->environment(['local', 'staging']) && Auth::user()->isAdmin),

                NavigationItem::make('pulse')
                    ->label('Pulse')
                    ->badge(fn(): string => '●')
                    ->badgeTooltip('Pulse provides real-time insights into your application\'s performance and health.')
                    ->url(fn(): string => route('pulse'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-heart')
                    ->group('Monitor')
                    ->visible(fn(): bool => !app()->environment('testing') && Auth::user()->isAdmin),

                NavigationItem::make('horizon')
                    ->label('Horizon')
                    ->badge(fn(): string => '●')
                    ->badgeTooltip('Horizon gives you a simple way to manage and monitor background tasks.')
                    ->url(fn(): string => route('horizon.index'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-lifebuoy')
                    ->group('Monitor')
                    ->visible(fn(): bool => !app()->environment('testing') && Auth::user()->isAdmin),
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
            ])
            ->plugins([
                 FilamentTranslatableFieldsPlugin::make()
                    ->supportedLocales([
                        'en' => 'English',
                        'ar' => 'Arabic',
                    ]),
            ]);
    }
}

function colors()
{
    return [
        'primary' => [
            50 => '#f0f9f4',
            100 => '#dcf2e4',
            200 => '#bce5cb',
            300 => '#8dd1a6',
            400 => '#52b96f',
            500 => '#007a3d',
            600 => '#006833',
            700 => '#00562a',
            800 => '#004522',
            900 => '#00391c',
            950 => '#001f0f',
        ],
        'secondary' => [
            50 => '#f0fdfc',
            100 => '#ccfbf1',
            200 => '#99f6e4',
            300 => '#5eead4',
            400 => '#2dd4bf',
            500 => '#03dac6',
            600 => '#0d9488',
            700 => '#018786',
            800 => '#134e4a',
            900 => '#134e4a',
            950 => '#042f2e',
        ],
    ];
}
