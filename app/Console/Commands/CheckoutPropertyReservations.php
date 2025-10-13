<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\DailyOccupancy;
use Carbon\Carbon;

class CheckoutPropertyReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:checkout-property';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks out property reservations for the day and updates occupancy.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();

        // Ambil reservasi dari pengguna properti yang checkout hari ini
        $reservations = Reservation::where('checkout_date', $today)
                                   ->where('source', 'properti') // Asumsi source 'properti' untuk pengguna properti
                                   ->get();

        if ($reservations->isEmpty()) {
            $this->info('Tidak ada reservasi dari properti yang checkout hari ini.');
            return;
        }

        foreach ($reservations as $reservation) {
            $dailyOccupancy = DailyOccupancy::where('property_id', $reservation->property_id)
                ->where('date', $today)
                ->first();

            if ($dailyOccupancy) {
                $rooms = $reservation->number_of_rooms;
                
                // Kurangi okupansi dari reservasi properti
                $dailyOccupancy->decrement('reservasi_properti', $rooms);
                $dailyOccupancy->decrement('occupied_rooms', $rooms);
            }
        }

        $this->info(count($reservations) . ' reservasi properti berhasil di-checkout.');
    }
}