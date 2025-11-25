<?php

namespace App\Events;

// app/Events/GastoCreated.php
namespace App\Events;

use App\Models\Gasto;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GastoCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $gasto;

    public function __construct(Gasto $gasto)
    {
        $this->gasto = $gasto;
    }

    public function broadcastOn()
    {
        return new Channel('reportes'); // canal público "reportes"
    }

    public function broadcastWith()
    {
        return [
            'id'     => $this->gasto->id,
            'monto'  => $this->gasto->monto,
            'fecha'  => $this->gasto->fecha->format('Y-m-d H:i:s'),
        ];
    }
    
}
