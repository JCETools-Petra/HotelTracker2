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
        Schema::table('categories', function (Blueprint $table) {
            // Cek dulu apakah kolomnya ada
            if (Schema::hasColumn('categories', 'property_id')) {
                // [PERBAIKAN] Langsung hapus kolomnya saja
                $table->dropColumn('property_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Jika di-rollback, tambahkan kembali kolomnya
            if (!Schema::hasColumn('categories', 'property_id')) {
                // Kita buat nullable untuk keamanan jika ada data
                $table->foreignId('property_id')->nullable()->constrained('properties')->after('name');
            }
        });
    }
};