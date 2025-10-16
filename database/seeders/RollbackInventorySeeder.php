<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RollbackInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Matikan pengecekan foreign key untuk sementara
        Schema::disableForeignKeyConstraints();

        $this->command->info('Menghapus data dari tabel inventories...');
        DB::table('inventories')->truncate();

        $this->command->info('Menghapus data dari tabel categories...');
        DB::table('categories')->truncate();

        // Nyalakan kembali pengecekan foreign key
        Schema::enableForeignKeyConstraints();

        $this->command->info('Rollback untuk InventoryCategoriesAndItemsSeeder selesai.');
    }
}