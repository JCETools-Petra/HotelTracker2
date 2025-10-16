<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SunnydayInnRestoInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        $propertyId = 13; // Sesuai permintaan untuk Sunnyday Inn

        // 1. Pastikan Kategori 'RESTO' ada dan dapatkan ID-nya
        // Jika belum ada, seeder akan membuatnya secara otomatis
        $categoryName = 'RESTO';
        $categoryCode = 'EO'; // Kita gunakan nama lengkap agar unik
        
        DB::table('categories')->updateOrInsert(
            ['category_code' => $categoryCode],
            ['name' => $categoryName, 'created_at' => $now, 'updated_at' => $now]
        );

        $restoCategory = DB::table('categories')->where('category_code', $categoryCode)->first();
        if (!$restoCategory) {
            $this->command->error('Gagal membuat atau menemukan kategori RESTO.');
            return;
        }
        $categoryId = $restoCategory->id;

        // 2. Hapus data inventaris lama untuk properti dan kategori ini agar tidak duplikat
        DB::table('inventories')->where('property_id', $propertyId)->where('category_id', $categoryId)->delete();

        // 3. Data mentah dari Anda
        $rawData = [
            ['nama' => 'DINNER PLATE', 'jumlah' => 200, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'DISSERT PLATE', 'jumlah' => 82, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'SOUP BOWL', 'jumlah' => 86, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'SOUCER SOUP CUP', 'jumlah' => 56, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'TEA CUP', 'jumlah' => 19, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'SOUCER TEA CUP', 'jumlah' => 45, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'JUICE GLASS', 'jumlah' => 11, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'WATER GOBLET', 'jumlah' => 30, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'PICHER AIR', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'DISPENSER SIRUP', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'PICHER FRESS MILK', 'jumlah' => 2, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'BLENDER', 'jumlah' => 3, 'satuan' => 'PCS', 'kondisi' => '1 BAIK', 'keterangan' => '2 RUSAK'],
            ['nama' => 'PEMANGGANG ROTI', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'SOUP TURING/LISTRIK', 'jumlah' => 2, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'SOUP TURING/STAINLESS', 'jumlah' => 2, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'DINNER SPOON', 'jumlah' => 140, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'DINNER FORK', 'jumlah' => 110, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'LEADLE SOUP', 'jumlah' => 4, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'WATER JUG', 'jumlah' => 3, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'SOUP SPOON', 'jumlah' => 137, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'PISAU SLAI', 'jumlah' => 4, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'KULKAS', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'SERVING TRAY', 'jumlah' => 5, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'KOTAK TISSUE', 'jumlah' => 14, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'AKRILIK MAKANAN', 'jumlah' => 16, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'BUFFET BREAKFAST/RESTO', 'jumlah' => 3, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'SHAKE', 'jumlah' => 2, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'TEKO KOPI BREAKFAST/SET', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'MESIN KOPI', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'GRINDER', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'TIMBANGAN', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'CREAMER JUG', 'jumlah' => 5, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'FREEZER ICED CREAM', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'CAPIT KRUPUK', 'jumlah' => 12, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'SERVING SPOON', 'jumlah' => 15, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'SERVING FORK', 'jumlah' => 12, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'CENTONG ES', 'jumlah' => 2, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'MEJA RESTO', 'jumlah' => 18, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'KURSI RESTO', 'jumlah' => 50, 'satuan' => 'PCS', 'kondisi' => '48 BAIK', 'keterangan' => '2 RUSAK'],
            ['nama' => 'KURSI RESTO/TINGGI', 'jumlah' => 18, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'KALKULATOR', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'ORBIT', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'GRIYO POS', 'jumlah' => 2, 'satuan' => 'PCS', 'kondisi' => '1 BAIK', 'keterangan' => '1 RUSAK'],
            ['nama' => 'DISPENSER ROKOK', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'KATLE JUG', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
            ['nama' => 'BOILER', 'jumlah' => 1, 'satuan' => 'PCS', 'kondisi' => 'RUSAK', 'keterangan' => ''],
            ['nama' => 'BUFFET AULA', 'jumlah' => 4, 'satuan' => 'PCS', 'kondisi' => 'BAIK', 'keterangan' => ''],
        ];

        $itemsToInsert = [];

        foreach ($rawData as $data) {
            $combinedConditions = $data['kondisi'] . ' ' . $data['keterangan'];

            // Cek jika ada format angka + kondisi (e.g., "1 BAIK 2 RUSAK")
            if (preg_match_all('/(\d+)\s+(BAIK|RUSAK)/i', $combinedConditions, $matches)) {
                foreach ($matches[1] as $index => $qty) {
                    $itemsToInsert[] = [
                        'item_code' => $categoryCode . '-' . strtoupper(Str::random(5)),
                        'property_id' => $propertyId,
                        'name' => $data['nama'],
                        'category_id' => $categoryId,
                        'stock' => (int)$qty,
                        'unit' => strtolower($data['satuan']),
                        'condition' => strtolower($matches[2][$index]),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            } else { // Jika formatnya sederhana (hanya 'BAIK' atau 'RUSAK')
                $itemsToInsert[] = [
                    'item_code' => $categoryCode . '-' . strtoupper(Str::random(5)),
                    'property_id' => $propertyId,
                    'name' => $data['nama'],
                    'category_id' => $categoryId,
                    'stock' => $data['jumlah'],
                    'unit' => strtolower($data['satuan']),
                    'condition' => strtolower($data['kondisi']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // 4. Masukkan semua data yang sudah diproses ke database
        DB::table('inventories')->insert($itemsToInsert);
        
        $this->command->info('Seeding data inventaris RESTO untuk Sunnyday Inn (property_id 13) berhasil.');
    }
}