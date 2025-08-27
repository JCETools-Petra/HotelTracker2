<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Panggil seeder lain yang mungkin Anda miliki di sini
        // \App\Models\User::factory(10)->create();

        $this->call([
            InventoryTableSeeder::class,
            // Anda bisa menambahkan seeder lain di sini di masa depan
            // Contoh: RoomTableSeeder::class,
        ]);
    }
}