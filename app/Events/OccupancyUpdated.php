<?php

namespace App\Events;

use App\Models\DailyOccupancy;
use App\Models\Property;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OccupancyUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $property;
    public $occupancy;

    /**
     * Create a new event instance.
     */
    public function __construct(Property $property, DailyOccupancy $occupancy)
    {
        $this->property = $property;
        $this->occupancy = $occupancy;
    }
}