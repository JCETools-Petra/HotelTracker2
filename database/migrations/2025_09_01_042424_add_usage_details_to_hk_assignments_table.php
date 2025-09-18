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
        Schema::table('hk_assignments', function (Blueprint $table) {
            // Menambahkan kolom untuk melacak item apa yang digunakan
            $table->foreignId('inventory_id')->nullable()->after('room_id')->constrained()->onDelete('set null');

            // Menambahkan kolom untuk melacak berapa banyak item yang digunakan
            $table->integer('quantity_used')->after('inventory_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hk_assignments', function (Blueprint $table) {
            $table->dropForeign(['inventory_id']);
            $table->dropColumn(['inventory_id', 'quantity_used']);
        });
    }
};