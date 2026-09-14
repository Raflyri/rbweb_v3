<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendLoginSuccessNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(Login $event): void
    {
        if ($event->user) {
            $event->user->notify(new \App\Notifications\Auth\LoginSuccess(
                now(),
                request()->ip() ?? 'Unknown'
            ));
        }
    }
}
