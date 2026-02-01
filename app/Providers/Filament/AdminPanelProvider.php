<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use App\Models\User;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->spa()
            // ->topNavigation()
            ->sidebarCollapsibleOnDesktop()
            ->brandName('e-POS')
            ->brandLogo(fn() => view('filament.app.logo'))
            ->brandLogoHeight('3rem')
            ->path('admin')
            ->sidebarWidth('15rem')
            ->maxContentWidth(MaxWidth::Full)
            ->login()
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Zinc,
                'info' => Color::Blue,
                'primary' => Color::Amber,
                'success' => Color::Green,
                'warning' => Color::Rose,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\ActiveShiftWidget::class,
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\DateAndTimeWidget::class,
            ])
            ->navigationItems([
                NavigationItem::make('Halaman Kasir')
                    ->icon('heroicon-o-shopping-cart')
                    ->url('/pos', shouldOpenInNewTab: true)
                    ->sort(1),
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
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                FilamentDeveloperLoginsPlugin::make()
                    ->enabled(app()->environment('local'))
                    ->users(fn() => User::pluck('email', 'name')->toArray())
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Transaksi'),
                NavigationGroup::make()
                    ->label('Master Data'),
                NavigationGroup::make()
                    ->label('Pengaturan'),
            ])
            ->renderHook(
                'panels::user-menu.before',
                fn(): string => view('filament.header.store-status')->render()
            );
    }
    public function register(): void
    {
        parent::register();
        $this->app->bind(
            \Filament\Http\Responses\Auth\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );
    }
}
