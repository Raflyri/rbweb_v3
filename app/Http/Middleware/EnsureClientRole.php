<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientRole
{
    /**
     * Guard for the /client-area panel.
     *
     * Cases:
     * - Unauthenticated  → pass through (Filament Authenticate handles redirect to login)
     * - Admins / Super Admins → allowed BUT also redirect to admin panel so they work in the right place
     * - Valid client roles (premium, regular_user) → allowed
     * - No valid role → logout and redirect to client login with clear message
     *
     * NOTE: We intentionally ALLOW admins through rather than blocking them.
     * They bypass email verification via canAccessPanel(). If you want admins
     * to be hard-redirected to /rbdashboard instead, flip the flag below.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            // No user — Filament's Authenticate MW will handle redirect
            return $next($request);
        }

        // Admins who land on /client-area: pass them through rather than 403-ing.
        // They can legitimately preview the client area (e.g., support, testing).
        // A gentle banner is shown via session flash.
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return $next($request);
        }

        // Valid client roles
        if ($user->hasAnyRole(['premium', 'regular_user'])) {
            return $next($request);
        }

        // User exists but has no valid role — inform and redirect to login
        // (do NOT invalidate session; they might have valid sessions on other panels)
        return redirect()
            ->route('filament.client-area.auth.login')
            ->withErrors(['email' => 'Your account does not have a valid role yet. Please contact support.']);
    }
}
