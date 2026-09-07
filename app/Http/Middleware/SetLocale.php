<?php

namespace App\Http\Middleware;

use App\Support\ArticleLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the visitor's chosen language to this request.
     *
     * The stored value is normalised and then checked against the locales that
     * are actually enabled right now. That second check is what makes turning a
     * language off safe: someone who picked Malay before it was switched off
     * still has 'ms' in their session, and without this they would be stranded
     * in a language the switcher no longer offers a way out of. Their stale
     * choice is dropped and they fall back to the site default (APP_LOCALE).
     *
     * The clamp deliberately lives here, at the HTTP door, rather than inside
     * ArticleLocale::current(): the ms/ja machinery underneath stays intact and
     * testable, it is simply no longer reachable from the outside.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('locale')) {
            $locale = ArticleLocale::normalize(Session::get('locale'));

            if (ArticleLocale::isEnabled($locale)) {
                App::setLocale($locale);
            } else {
                Session::forget('locale');
            }
        }

        return $next($request);
    }
}
