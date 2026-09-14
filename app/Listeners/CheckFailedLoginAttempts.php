<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CheckFailedLoginAttempts
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(Failed $event): void
    {
        if ($event->user) {
            $key = 'login_failures_' . $event->user->id . '_' . request()->ip();
            $attempts = \Illuminate\Support\Facades\Cache::increment($key);
            
            if ($attempts === 1) {
                // Set expiry for the failures count (e.g., 30 minutes)
                \Illuminate\Support\Facades\Cache::put($key, 1, now()->addMinutes(30));
            }

            if ($attempts === 3) {
                $event->user->notify(new \App\Notifications\Auth\LoginFailedWarning(
                    now(),
                    request()->ip() ?? 'Unknown'
                ));
            }
        }
    }
}
