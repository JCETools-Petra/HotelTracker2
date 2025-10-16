<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cek dulu apakah kolomnya sudah ada atau belum
        if (!Schema::hasColumn('categories', 'property_id')) {
            Schema::table('categories', function (Blueprint $table) {
                // Jika tidak ada, baru tambahkan kolomnya
                $table->foreignId('property_id')->constrained('properties')->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cek dulu apakah kolomnya ada
        if (Schema::hasColumn('categories', 'property_id')) {
            Schema::table('categories', function (Blueprint $table) {
                // Jika ada, baru hapus
                $table->dropForeign(['property_id']);
                $table->dropColumn('property_id');
            });
        }
    }
};