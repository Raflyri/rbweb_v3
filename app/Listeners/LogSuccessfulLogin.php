<?php

namespace App\Listeners;

use App\Models\AuthenticationLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Stevebauman\Location\Facades\Location;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        // Only track App\Models\User logins — ignore other guards/models
        if (! $event->user instanceof User) {
            return;
        }

        /** @var \App\Models\User $user */
        $user = $event->user;
        $ip   = request()->ip();
        $country  = null;
        $city     = null;

        // Geo-lookup is wrapped in try/catch so a slow/failed API call
        // never blocks or errors the login flow.
        try {
            $position = Location::get($ip);
            $country  = $position?->countryName;
            $city     = $position?->cityName;
        } catch (\Throwable) {
            // Silently skip — geo is nice-to-have, not critical
        }

        AuthenticationLog::create([
            'user_id'    => $user->id,
            'event'      => 'login',
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'country'    => $country,
            'city'       => $city,
            'logged_at'  => now(),
        ]);
    }
}
