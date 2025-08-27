<?php

namespace App\Listeners\Illuminate\Auth\Listeners;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEmailVerificationNotification implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        // Periksa apakah model User yang terdaftar memerlukan verifikasi email
        // dan apakah emailnya belum diverifikasi.
        if ($event->user instanceof MustVerifyEmail && ! $event->user->hasVerifiedEmail()) {
            // Kirim notifikasi verifikasi email.
            // Metode ini sudah disediakan oleh Laravel.
            $event->user->sendEmailVerificationNotification();
        }
    }
}
