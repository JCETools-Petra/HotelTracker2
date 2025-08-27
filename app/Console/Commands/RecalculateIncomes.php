<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyIncome;

class RecalculateIncomes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'income:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates totals for all daily income records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recalculation of all daily incomes...');

        $allIncomes = DailyIncome::all();

        if ($allIncomes->isEmpty()) {
            $this->info('No income records found. Nothing to do.');
            return;
        }

        $progressBar = $this->output->createProgressBar(count($allIncomes));
        $progressBar->start();

        foreach ($allIncomes as $income) {
            // ======================= AWAL PERBAIKAN KALKULASI =======================
            $property = $income->property;

            // Kalkulasi ulang total kamar terjual
            $total_rooms_sold =
                ($income->offline_rooms ?? 0) +
                ($income->online_rooms ?? 0) +
                ($income->ta_rooms ?? 0) +
                ($income->gov_rooms ?? 0) +
                ($income->corp_rooms ?? 0) +
                ($income->compliment_rooms ?? 0) +
                ($income->house_use_rooms ?? 0) +
                ($income->afiliasi_rooms ?? 0); // <-- DATA AFILIASI DITAMBAHKAN

            // Kalkulasi ulang total pendapatan kamar
            $total_rooms_revenue =
                ($income->offline_room_income ?? 0) +
                ($income->online_room_income ?? 0) +
                ($income->ta_income ?? 0) +
                ($income->gov_income ?? 0) +
                ($income->corp_income ?? 0) +
                ($income->compliment_income ?? 0) +
                ($income->house_use_income ?? 0) +
                ($income->afiliasi_room_income ?? 0); // <-- DATA AFILIASI DITAMBAHKAN

            // Kalkulasi ulang total pendapatan F&B
            $total_fb_revenue =
                ($income->breakfast_income ?? 0) +
                ($income->lunch_income ?? 0) +
                ($income->dinner_income ?? 0);

            // Kalkulasi ulang TOTAL PENDAPATAN KESELURUHAN
            $total_revenue = $total_rooms_revenue + $total_fb_revenue + ($income->mice_income ?? 0) + ($income->others_income ?? 0);

            // Kalkulasi ulang ARR dan Occupancy
            $arr = ($total_rooms_sold > 0) ? ($total_rooms_revenue / $total_rooms_sold) : 0;
            $occupancy = ($property && $property->total_rooms > 0) ? ($total_rooms_sold / $property->total_rooms) * 100 : 0;
            // ======================= AKHIR PERBAIKAN KALKULASI =======================

            // Simpan nilai yang sudah benar tanpa memicu event lain
            $income->total_rooms_sold = $total_rooms_sold;
            $income->total_rooms_revenue = $total_rooms_revenue;
            $income->total_fb_revenue = $total_fb_revenue;
            $income->total_revenue = $total_revenue;
            $income->arr = $arr;
            $income->occupancy = $occupancy;
            $income->saveQuietly(); // Menyimpan tanpa memicu event/observer

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nRecalculation complete for " . count($allIncomes) . " records.");
    }
}