<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SunnydayInnKitchenInventorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $propertyId = 13; // Sesuai permintaan untuk Sunnyday Inn

        // 1. Pastikan Kategori "KITCHEN TOOLS & EQUIPMENT" ada dan dapatkan ID-nya
        $categoryName = 'KITCHEN TOOLS & EQUIPMENT';
        $categoryCode = 'KTE'; // Sesuai standar kode dari seeder sebelumnya
        
        DB::table('categories')->updateOrInsert(
            ['category_code' => $categoryCode],
            ['name' => $categoryName, 'created_at' => $now, 'updated_at' => $now]
        );

        $kitchenCategory = DB::table('categories')->where('category_code', $categoryCode)->first();
        if (!$kitchenCategory) {
            $this->command->error('Gagal membuat atau menemukan kategori KITCHEN.');
            return;
        }
        $categoryId = $kitchenCategory->id;

        // 2. Hapus data inventaris lama untuk properti dan kategori ini agar tidak duplikat
        DB::table('inventories')->where('property_id', $propertyId)->where('category_id', $categoryId)->delete();

        // 3. Data mentah dari Anda (sudah di-parsing)
        // Data ini mengasumsikan format: 'NAMA BARANG', JUMLAH, 'KETERANGAN KONDISI'
        $rawData = [
            ['nama' => 'botol sc 1 litter', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Nampan plastik uk kecil', 'jumlah' => 5, 'ket' => ''],
            ['nama' => 'Nampan plastik uk sedang', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'Nampan plastik uk besar', 'jumlah' => 5, 'ket' => ''],
            ['nama' => 'Mangkok benin u/ es', 'jumlah' => 4, 'ket' => ''],
            ['nama' => 'Copper 1 unit', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Blander 1 unit', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Panci kuning', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Cetakan nasi krucut', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'alat serut lebar', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Alat serut panjang', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Sendok nasi kecil', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'Sendok nasi garpu', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Sendok nasi besar', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Penjepit makanan', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Lidle kecil', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Sutle kayu', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'tevlon untuk telur', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Tapper ware bulat', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Lidle sedang', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Lidle besar', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Lidle es', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Sutle stanlist sedang', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Sutle stanlist besar', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Saringan kecil halus', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'keranjang plastik', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Saringan stanlis bulat', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'mangkok stanlis', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'baskom stanlis kecil dalam', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Baskom stanlis sedang dalam', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'baskom stanlis besar dalam', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'Baskom stanlis kecil dangkal', 'jumlah' => 4, 'ket' => ''],
            ['nama' => 'Baskom stanlis sedang dangkal', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Baskom stanlis besar dangkal', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Insert pendek', 'jumlah' => 16, 'ket' => ''],
            ['nama' => 'Insert mini', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'Insert panjang dalam', 'jumlah' => 12, 'ket' => ''],
            ['nama' => 'Insert panjang dangkal', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Dandang stanlis kecil', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Dandang stanlis sedang', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Dandang stanlis besar', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Dandang almunium kecil', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Dandang almunium sedang', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Dandang almunium besar', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Soup stanlis Sedang', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Soup turing', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Baskom plastik kecil', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Baskom plastik sedang', 'jumlah' => 7, 'ket' => ''],
            ['nama' => 'Plastik kotak kecil', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'Plastik kotan sedang', 'jumlah' => 5, 'ket' => ''],
            ['nama' => 'Plastik kotak besar', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'mangkok soup', 'jumlah' => 4, 'ket' => 'gompel'],
            ['nama' => 'panci merah kecil', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Pitchere akrilick', 'jumlah' => 10, 'ket' => ''],
            ['nama' => 'taperware untuk belhotel', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Toples kaca', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Toples plastik', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Toples 25 liter', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Toples 10 litter', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Magicom', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'talenan kecil', 'jumlah' => 1, 'ket' => 'rusak'],
            ['nama' => 'Talenan sedang', 'jumlah' => 1, 'ket' => 'rusak'],
            ['nama' => 'Talenan besar', 'jumlah' => 1, 'ket' => 'rusak'],
            ['nama' => 'Gilingan batu', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Tabung gas', 'jumlah' => 7, 'ket' => ''],
            ['nama' => 'Megicom kecil', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Tampah rotan', 'jumlah' => 5, 'ket' => ''],
            ['nama' => 'Tampah plastik merah', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Tampah plastik coklat', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Termos nasi besar', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Termon nasi kecil', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'kompor work', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'kompor gas', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Kampak(potong daging)', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Pisau kecil', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Pisau sedang', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Pisau buah', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'gunting', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Microwafe', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Teflon kecil', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Teflon sedang', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Teflon besar', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Teflon pemanggang', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'Wajan baja kecil', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Wajan baja sedang', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Wajan baja besar', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Showcase 3 pintu 1 unit', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Showcase 2 pintu 1 unit(rusak)', 'jumlah' => 1, 'ket' => 'rusak'],
            ['nama' => 'Kulkas 2 pintu 1 unit', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Topes coco crunch', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Toples roti', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Kompor fortable', 'jumlah' => 5, 'ket' => ''],
            ['nama' => 'Keranjang biru', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'Keranjang kuning', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Keranjang merah', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'piring segi panjang', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Piring segi pendek', 'jumlah' => 5, 'ket' => ''],
            ['nama' => 'Piring kotak', 'jumlah' => 8, 'ket' => ''],
            ['nama' => 'Piring bulat kecil', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'Piring bulat sedang', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Piring bulat besar', 'jumlah' => 5, 'ket' => ''],
            ['nama' => 'Gelas acar', 'jumlah' => 5, 'ket' => ''],
            ['nama' => 'Taperware kotak', 'jumlah' => 6, 'ket' => ''],
            ['nama' => 'Wajan almunium kecil', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Wajan almunium sedang', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Wajan almunium besar', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Tong sampah', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Sapu', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Serok sampah', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Kain pel', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Serut bulat', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'Duble disk kecil', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'Duble disk petak', 'jumlah' => 4, 'ket' => ''],
            ['nama' => 'scuisze', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'telenan putih ( buah )', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'talenan kayu bulat potong ayam', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'saringan halus uk besar', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'saringan kasar uk besar', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'sapu wajan', 'jumlah' => 3, 'ket' => ''],
            ['nama' => 'mangkok acar & sambal', 'jumlah' => 10, 'ket' => ''],
            ['nama' => 'sc disk', 'jumlah' => 12, 'ket' => ''],
            ['nama' => 'tong sampah roda (besar)', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'megic com 23-25 liter', 'jumlah' => 2, 'ket' => ''],
            ['nama' => 'compor 6 bunner', 'jumlah' => 1, 'ket' => ''],
            ['nama' => 'oven gas lpg ( 2 tingkat )', 'jumlah' => 1, 'ket' => ''],
        ];

        $itemsToInsert = [];
        foreach ($rawData as $data) {
            // Jika jumlah tidak ada (kosong), lewati item ini
            if (empty($data['jumlah'])) {
                continue;
            }

            $itemsToInsert[] = [
                'item_code'                 => $categoryCode . '-' . strtoupper(Str::random(5)),
                'property_id'               => $propertyId,
                'name'                      => $data['nama'],
                'category_id'               => $categoryId,
                'stock'                     => (int)$data['jumlah'],
                'unit'                      => 'pcs', // Default 'pcs' karena tidak ada di data
                'condition'                 => (stripos($data['ket'], 'rusak') !== false || stripos($data['ket'], 'gompel') !== false) ? 'rusak' : 'baik',
                'created_at'                => $now,
                'updated_at'                => $now,
                'unit_price'                => 0.00,
                'minimum_standard_quantity' => 0,
                'purchase_date'             => null,
                'specification'             => null,
            ];
        }

        // 4. Masukkan semua data yang sudah diproses ke database
        DB::table('inventories')->insert($itemsToInsert);
        
        $this->command->info('Seeding data KITCHEN untuk Sunnyday Inn (property_id 13) berhasil.');
    }
}