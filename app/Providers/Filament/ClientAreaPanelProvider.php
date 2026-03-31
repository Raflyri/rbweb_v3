<?php

namespace App\Providers\Filament;

use App\Filament\ClientArea\Widgets\WelcomeBannerWidget;
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
            // isRequired: true (the default) activates Filament's built-in
            // EnsureEmailIsVerified middleware that redirects unverified users to
            // /client-area/email-verification/prompt AFTER canAccessPanel() passes.
            // canAccessPanel() must NOT check hasVerifiedEmail() — doing so causes
            // a 403 before the redirect can fire.
            ->emailVerification()

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
                // EnsureClientRole is intentionally removed: canAccessPanel() is the
                // correct Filament-idiomatic gate. The custom middleware was duplicating
                // this check and also ran on the email-verification prompt route, which
                // caused a secondary redirect loop for newly-registered users.
            ]);
    }
}
