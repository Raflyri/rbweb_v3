<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to enforce vital HTTP Security Headers on all web responses.
 *
 * Protects against:
 * - Clickjacking (X-Frame-Options)
 * - MIME-type confusion / sniffing attacks (X-Content-Type-Options)
 * - Token & PII referrer leakage (Referrer-Policy)
 * - Man-in-the-Middle SSL stripping (Strict-Transport-Security)
 * - Unauthorized hardware/sensor access (Permissions-Policy)
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Clickjacking defense
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Protect against referrer leaking sensitive tokens (e.g. order public_token)
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict browser features not needed by the website
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // HTTP Strict Transport Security (HSTS) - only enforce when request is HTTPS / proxied via Cloudflare
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
