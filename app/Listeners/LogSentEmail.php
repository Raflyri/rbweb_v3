<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\InteractsWithQueue;

class LogSentEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(MessageSent $event): void
    {
        $message = $event->message;
        $subject = $message->getSubject();
        
        $toAddresses = [];
        foreach ($message->getTo() as $address) {
            $toAddresses[] = $address->getAddress();
        }

        \App\Models\EmailLog::create([
            'to_email' => implode(', ', $toAddresses),
            'subject'  => $subject,
            'status'   => 'sent',
            'error_message' => null,
        ]);
    }
}
