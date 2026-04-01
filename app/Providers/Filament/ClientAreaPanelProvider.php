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
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
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
            // isRequired: false → keeps the /email-verification/verify route registered
            // (so links in the email still resolve) but removes the enforcement redirect.
            // Unverified users can now enter the dashboard; a persistent banner widget
            // and Livewire component handle the nudge to verify.
            ->emailVerification(isRequired: false)

            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->databaseNotifications()
            ->colors([
                'primary' => Color::Sky,
            ])
            ->unsavedChangesAlerts()
            ->viteTheme('resources/css/filament/client-area/theme.css')
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

            // ── Persistent email verification banner ─────────────────────
            // Injects the Livewire ResendVerificationEmail component at the top
            // of EVERY authenticated page in this panel (not just the dashboard).
            // The component itself guards against rendering when already verified.
            ->renderHook(
                PanelsRenderHook::CONTENT_START,
                fn (): View => view('filament.client-area.email-verification-banner'),
            )

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
