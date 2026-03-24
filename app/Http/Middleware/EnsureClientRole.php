<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientRole
{
    /**
     * Middleware untuk panel /client-area.
     * Semua role valid (super_admin, admin, premium, regular_user) diizinkan.
     * User tanpa role akan di-logout dan diarahkan ke halaman login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasAnyRole(['super_admin', 'admin', 'premium', 'regular_user'])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('filament.client-area.auth.login')
                ->withErrors(['email' => 'Akun kamu belum memiliki role yang valid. Silakan hubungi admin.']);
        }

        return $next($request);
    }
}
