<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureClientRole;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
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

class ClientAreaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('client-area')
            ->path('client-area')
            ->login()
            ->registration(\App\Filament\ClientArea\Pages\Auth\Register::class) // ✅ Custom: auto-assign role
            ->passwordReset()                   // ✅ Reset password diaktifkan
            ->emailVerification()               // ✅ Verifikasi email diaktifkan
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->databaseNotifications()           // ✅ Notifikasi in-app diaktifkan
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(
                in: app_path('Filament/ClientArea/Resources'),
                for: 'App\Filament\ClientArea\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/ClientArea/Pages'),
                for: 'App\Filament\ClientArea\Pages'
            )
            ->pages([Dashboard::class])
            ->discoverWidgets(
                in: app_path('Filament/ClientArea/Widgets'),
                for: 'App\Filament\ClientArea\Widgets'
            )
            ->widgets([AccountWidget::class])
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
                EnsureClientRole::class,        // ✅ Isolasi role
            ]);
    }
}
