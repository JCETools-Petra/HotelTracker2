<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\Booking;
use Carbon\Carbon;

class SunnydayInnJanuaryMiceSeeder extends Seeder
{
    public function run()
    {
        $property = Property::where('name', 'Sunnyday Inn')->first();
        if (!$property) {
            $this->command->error('Property "Sunnyday Inn" not found. MICE seeder cannot run.');
            return;
        }

        $this->command->info('Seeding January 2025 MICE Bookings for Sunnyday Inn...');

        DB::transaction(function () use ($property) {
            // Bersihkan data bookings MICE Januari 2025 untuk property ini
            Booking::where('property_id', $property->id)
                ->whereYear('event_date', 2025)
                ->whereMonth('event_date', 1)
                ->delete();

            // Tiga event (contoh realistis) total = 111,050,413
            $bookings = [
                [
                    'client_name'   => 'Dinas Pendidikan Merauke',
                    'event_type'    => 'Rapat Kerja Tahunan',
                    'event_date'    => '2025-01-10',
                    'participants'  => 150,
                    'total_price'   => 45_500_000,
                    'status'        => 'Booking Pasti',
                    'payment_status'=> 'Paid',
                    'down_payment'  => 45_500_000,
                    'notes'         => 'Termasuk coffee break dan makan siang.'
                ],
                [
                    'client_name'   => 'PT. Sinar Papua Sejati',
                    'event_type'    => 'Product Launching',
                    'event_date'    => '2025-01-18',
                    'participants'  => 80,
                    'total_price'   => 28_050_413,
                    'status'        => 'Booking Pasti',
                    'payment_status'=> 'Paid',
                    'down_payment'  => 28_050_413,
                    'notes'         => 'Paket full-day, include backdrop.'
                ],
                [
                    'client_name'   => 'Keluarga Besar Situmorang',
                    'event_type'    => 'Acara Pernikahan',
                    'event_date'    => '2025-01-25',
                    'participants'  => 250,
                    'total_price'   => 37_500_000,
                    'status'        => 'Booking Pasti',
                    'payment_status'=> 'Paid',
                    'down_payment'  => 37_500_000,
                    'notes'         => 'Termasuk dekorasi dan band.'
                ],
            ];

            foreach ($bookings as $i => $b) {
                Booking::create([
                    'property_id'      => $property->id,
                    'booking_number'   => sprintf('MICE-202501-%04d', $i + 1),
                    'booking_date'     => Carbon::parse($b['event_date'])->copy()->subDays(mt_rand(5, 10))->toDateString(),
                    'client_name'      => $b['client_name'],
                    'event_type'       => $b['event_type'],
                    'event_date'       => $b['event_date'],
                    'start_time'       => '09:00:00',
                    'end_time'         => '17:00:00',
                    'participants'     => $b['participants'],
                    'person_in_charge' => 'Bapak Budi Santoso',
                    'status'           => $b['status'],
                    'payment_status'   => $b['payment_status'],
                    'total_price'      => number_format($b['total_price'], 2, '.', ''),
                    'down_payment'     => number_format($b['down_payment'], 2, '.', ''),
                    'notes'            => $b['notes'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                    'mice_category_id' => null,
                    'room_id'          => null,
                ]);
            }
        });

        $totalMice = Booking::where('property_id', $property->id)
            ->whereYear('event_date', 2025)->whereMonth('event_date', 1)
            ->sum('total_price');

        $this->command->info('MICE Bookings seeding complete. Target MICE: 111,050,413 | Actual: ' . number_format($totalMice, 0, ',', '.'));
    }
}
