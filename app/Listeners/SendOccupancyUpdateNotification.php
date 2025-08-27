<?php

namespace App\Listeners;

use App\Events\OccupancyUpdated;
use App\Mail\OccupancyUpdateNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOccupancyUpdateNotification
{
    /**
     * Handle the event.
     */
    public function handle(OccupancyUpdated $event): void
    {
        // 1. Ambil semua user dengan peran 'online_ecommerce'
        $ecommerceUsers = User::where('role', 'online_ecommerce')->get();

        // 2. Kirim email ke setiap user
        foreach ($ecommerceUsers as $user) {
            Mail::to($user->email)->send(
                new OccupancyUpdateNotification($event->property, $event->occupancy)
            );
        }
    }
}