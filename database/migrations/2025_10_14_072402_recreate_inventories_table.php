<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Metode ini akan menghapus tabel lama dan membuat yang baru.
     */
    public function up(): void
    {
        // 1. Matikan sementara pengecekan foreign key
        Schema::disableForeignKeyConstraints();
    
        // 2. Hapus tabel 'inventories' jika sudah ada
        Schema::dropIfExists('inventories');
    
        // 3. Buat kembali tabel 'inventories' dengan struktur yang benar
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->foreignId('property_id')->nullable()->constrained('properties')->onDelete('set null');
            $table->string('name');
            $table->string('specification')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->integer('stock')->default(0);
            $table->string('unit');
            $table->enum('condition', ['baik', 'rusak'])->default('baik');
            $table->timestamps();
        });
    
        // 4. Aktifkan kembali pengecekan foreign key
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     * Metode ini akan mengembalikan struktur tabel lama jika kamu melakukan rollback.
     */
    public function down(): void
    {
        // Hapus tabel yang baru dibuat
        Schema::dropIfExists('inventories');

        // (Opsional) Kamu bisa membuat ulang skema lama di sini jika butuh rollback
        // Tapi untuk kasus ini, dropIfExists sudah cukup aman.
    }
};