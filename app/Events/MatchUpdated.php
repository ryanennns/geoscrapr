<?php

namespace App\Events;

use App\Models\WorldCupMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(private WorldCupMatch $match)
    {
        //
    }

    public function broadcastWith(): array
    {
        return ['match' => $this->match];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('world-cup-2025'),
        ];
    }
}
