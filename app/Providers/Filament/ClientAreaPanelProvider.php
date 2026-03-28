<?php

namespace App\Providers\Filament;

use App\Filament\ClientArea\Widgets\WelcomeBannerWidget;
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
            ->registration(\App\Filament\ClientArea\Pages\Auth\Register::class)
            ->passwordReset()

            // ── Email verification ────────────────────────────────────────
            // We enable the email verification PROMPT page so the route exists,
            // but we do NOT set isRequired=true here. Instead, canAccessPanel()
            // in User.php skips the check for admins. Regular users are already
            // required to verify via canAccessPanel() returning false until verified.
            ->emailVerification(isRequired: false)

            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->databaseNotifications()
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
            ->widgets([
                WelcomeBannerWidget::class,
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
                Authenticate::class,
                EnsureClientRole::class,
            ]);
    }
}
