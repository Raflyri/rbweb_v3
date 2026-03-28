<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Guard for the /rbdashboard panel.
     *
     * - Unauthenticated users  → redirect to admin login (handled upstream by Filament's Authenticate middleware, but kept as safety net)
     * - Authenticated non-admins → redirect gracefully to their correct panel (/client-area) instead of 403
     * - Authenticated admins    → pass through
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            // Not logged in — Filament's Authenticate MW handles this, but just in case
            return redirect()->route('filament.admin.auth.login');
        }

        if (! $user->hasAnyRole(['super_admin', 'admin'])) {
            // A client accidentally hit /rbdashboard — send them home gracefully
            session()->flash('status', 'You do not have access to the Admin Dashboard.');

            return redirect()->route('filament.client-area.pages.dashboard');
        }

        return $next($request);
    }
}
