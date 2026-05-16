<?php

namespace App\Listeners;

use App\Events\ContactScoreProcessed;

class LogContactScoreProcessed
{
    public function handle(ContactScoreProcessed $event): void
    {
        $payload = [
            'id' => $event->contact->getId(),
            'email' => $event->contact->getEmail()->value(),
            'score' => $event->contact->getScore(),
            'status' => $event->contact->getStatus()->value(),
        ];

        $line = sprintf("[%s] %s\n", now()->toDateTimeString(), json_encode($payload, JSON_UNESCAPED_UNICODE));
        file_put_contents(storage_path('logs/contact.log'), $line, FILE_APPEND);
    }
}
