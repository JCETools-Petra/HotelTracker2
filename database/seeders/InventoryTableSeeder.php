<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;

class InventoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $inventories = [
            // Data untuk ROOM AMENITIES
            [
                'name' => 'Sabun',
                'category' => 'ROOM AMENITIES',
                'quantity' => 1000,
                'unit' => 'pcs',
                'price' => 2000.00,
            ],
            [
                'name' => 'Shampo',
                'category' => 'ROOM AMENITIES',
                'quantity' => 1000,
                'unit' => 'pcs',
                'price' => 2500.00,
            ],
            [
                'name' => 'Sikat Gigi',
                'category' => 'ROOM AMENITIES',
                'quantity' => 1000,
                'unit' => 'pcs',
                'price' => 3000.00,
            ],
            [
                'name' => 'Pasta Gigi',
                'category' => 'ROOM AMENITIES',
                'quantity' => 1000,
                'unit' => 'tube',
                'price' => 3500.00,
            ],
            [
                'name' => 'Sisir',
                'category' => 'ROOM AMENITIES',
                'quantity' => 1000,
                'unit' => 'pcs',
                'price' => 1500.00,
            ],

            // Data untuk HOUSEKEEPING CHEMICAL
            [
                'name' => 'Pembersih Kaca',
                'category' => 'HOUSEKEEPING CHEMICAL',
                'quantity' => 50,
                'unit' => 'liter',
                'price' => 50000.00,
            ],
            [
                'name' => 'Pembersih Lantai',
                'category' => 'HOUSEKEEPING CHEMICAL',
                'quantity' => 50,
                'unit' => 'liter',
                'price' => 45000.00,
            ],
            [
                'name' => 'Karbol',
                'category' => 'HOUSEKEEPING CHEMICAL',
                'quantity' => 50,
                'unit' => 'liter',
                'price' => 40000.00,
            ],
            [
                'name' => 'Pewangi Ruangan',
                'category' => 'HOUSEKEEPING CHEMICAL',
                'quantity' => 50,
                'unit' => 'liter',
                'price' => 60000.00,
            ],

            // Data untuk GUEST SUPPLIES
            [
                'name' => 'Teh Celup',
                'category' => 'GUEST SUPPLIES',
                'quantity' => 2000,
                'unit' => 'pcs',
                'price' => 1000.00,
            ],
            [
                'name' => 'Kopi Sachet',
                'category' => 'GUEST SUPPLIES',
                'quantity' => 2000,
                'unit' => 'pcs',
                'price' => 1500.00,
            ],
            [
                'name' => 'Gula Sachet',
                'category' => 'GUEST SUPPLIES',
                'quantity' => 2000,
                'unit' => 'pcs',
                'price' => 500.00,
            ],
        ];

        // Masukkan data ke database
        foreach ($inventories as $inventory) {
            Inventory::create($inventory);
        }
    }
}