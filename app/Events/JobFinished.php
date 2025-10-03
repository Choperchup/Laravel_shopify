<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobFinished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $jobId;

    /**
     * Create a new event instance.
     */
    public function __construct($jobId)
    {
        $this->jobId = $jobId;
    }

    public function broadcastOn()
    {
        return new Channel('jobs');
    }

    public function broadcastAs()
    {
        return 'JobFinished';
    }
}
