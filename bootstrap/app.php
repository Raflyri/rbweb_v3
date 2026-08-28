<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The app is only ever reached through Cloudflare's proxy on this
        // host, which terminates TLS and forwards to the origin over plain
        // HTTP. Without this, Laravel trusts none of the X-Forwarded-*
        // headers Cloudflare sets, so it thinks every request is HTTP —
        // Livewire then generates its AJAX endpoint as http://, and browsers
        // silently block that as mixed content on an https:// page. That is
        // what broke every Livewire-powered form (login included).
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
