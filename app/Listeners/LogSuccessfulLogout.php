<?php

namespace App\Listeners;

use App\Models\AuthenticationLog;
use App\Models\User;
use Illuminate\Auth\Events\Logout;
use Stevebauman\Location\Facades\Location;

class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        // Only track App\Models\User logouts
        if (! $event->user instanceof User) {
            return;
        }

        /** @var \App\Models\User $user */
        $user = $event->user;
        $ip   = request()->ip();
        $country = null;
        $city    = null;

        try {
            $position = Location::get($ip);
            $country  = $position?->countryName;
            $city     = $position?->cityName;
        } catch (\Throwable) {
            //
        }

        AuthenticationLog::create([
            'user_id'    => $user->id,
            'event'      => 'logout',
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'country'    => $country,
            'city'       => $city,
            'logged_at'  => now(),
        ]);
    }
}
