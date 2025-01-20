<?php

namespace App\Listeners;

use App\Events\ClientCreated;
use App\Mail\ClientCreatedEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendClientCreatedEmail implements ShouldQueue
{
    public function handle(ClientCreated $event): void
    {
        Mail::to($event->email)->send(
            new ClientCreatedEmail(
                $event->name,
                $event->email,
                $event->password,
            )
        );

        Log::info('Email sent successfully to client '. $event->email);
    }
}
