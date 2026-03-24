<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Middleware khusus panel /rbdashboard.
     * Hanya mengizinkan user dengan role super_admin atau admin.
     * User dengan role lain (premium, regular_user) akan di-logout dan diarahkan ke login admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasAnyRole(['super_admin', 'admin'])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['email' => 'Akses ditolak. Hanya Super Admin dan Admin yang dapat mengakses dashboard ini.']);
        }

        return $next($request);
    }
}
