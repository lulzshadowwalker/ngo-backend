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
                    ->badge(fn (): string => '●')
                    ->badgeTooltip('Telescope helps track what happens behind the scenes in your app.')
                    ->url(fn (): string => app()->environment(['local', 'staging', 'production']) ? route('telescope') : '#', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-chart-bar-square')
                    ->group('Monitor')
                    ->visible(fn (): bool => ! app()->environment('testing') && Auth::user()->isAdmin),

                NavigationItem::make('pulse')
                    ->label('Pulse')
                    ->badge(fn (): string => '●')
                    ->badgeTooltip('Pulse provides real-time insights into your application\'s performance and health.')
                    ->url(fn (): string => route('pulse'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-heart')
                    ->group('Monitor')
                    ->visible(fn (): bool => ! app()->environment('testing') && Auth::user()->isAdmin),

                NavigationItem::make('horizon')
                    ->label('Horizon')
                    ->badge(fn (): string => '●')
                    ->badgeTooltip('Horizon gives you a simple way to manage and monitor background tasks.')
                    ->url(fn (): string => route('horizon.index'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-lifebuoy')
                    ->group('Monitor')
                    ->visible(fn (): bool => ! app()->environment('testing') && Auth::user()->isAdmin),
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
            50 => '#edf7fb',
            100 => '#daeef7',
            200 => '#b8deef',
            300 => '#89c5e3',
            400 => '#004A6F',
            500 => '#2e8bc2',
            600 => '#1f70a4',
            700 => '#1a5b85',
            800 => '#184d6e',
            900 => '#19415c',
            950 => '#004a6f',
        ],
        'secondary' => [
            50 => '#f0f9fc',
            100 => '#def2f8',
            200 => '#c0e5f2',
            300 => '#96d2e9',
            400 => '#64b5dc',
            500 => '#429ace',
            600 => '#327db7',
            700 => '#2c6594',
            800 => '#2a557a',
            900 => '#264765',
            950 => '#1a2e43',
        ],
        'success' => [
            50 => '#f0fdf4',
            100 => '#dcfce7',
            200 => '#bbf7d0',
            300 => '#86efac',
            400 => '#4ade80',
            500 => '#22c55e',
            600 => '#16a34a',
            700 => '#15803d',
            800 => '#166534',
            900 => '#14532d',
            950 => '#052e16',
        ],
        'warning' => [
            50 => '#fffbeb',
            100 => '#fef3c7',
            200 => '#fde68a',
            300 => '#fcd34d',
            400 => '#fbbf24',
            500 => '#ffc107',
            600 => '#d97706',
            700 => '#b45309',
            800 => '#92400e',
            900 => '#78350f',
            950 => '#451a03',
        ],
        'danger' => [
            50 => '#fef2f2',
            100 => '#fee2e2',
            200 => '#fecaca',
            300 => '#fca5a5',
            400 => '#f87171',
            500 => '#ef4444',
            600 => '#dc2626',
            700 => '#b91c1c',
            800 => '#991b1b',
            900 => '#7f1d1d',
            950 => '#450a0a',
        ],
        'gray' => [
            50 => '#f9fafb',
            100 => '#f3f4f6',
            200 => '#e5e7eb',
            300 => '#d1d5db',
            400 => '#9ca3af',
            500 => '#6b7280',
            600 => '#4b5563',
            700 => '#374151',
            800 => '#1f2937',
            900 => '#111827',
            950 => '#030712',
        ],
    ];
}
