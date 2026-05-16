<?php

namespace App\Events;

use App\Core\Domain\Contact\Entities\Contact;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class ContactScoreProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Contact $contact)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('contacts.' . $this->contact->getId());
    }

    public function broadcastWith(): array
    {
        return $this->contact->toArray();
    }

    public function broadcastAs(): string
    {
        return 'ContactScoreProcessed';
    }
}
