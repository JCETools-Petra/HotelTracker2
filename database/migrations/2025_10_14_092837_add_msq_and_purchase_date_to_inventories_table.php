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
        Schema::table('inventories', function (Blueprint $table) {
            // Tambahkan kolom MSQ setelah 'stock'
            $table->integer('minimum_standard_quantity')->default(0)->after('stock');
            
            // Tambahkan kolom tanggal pembelian setelah 'condition'
            $table->date('purchase_date')->nullable()->after('condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['minimum_standard_quantity', 'purchase_date']);
        });
    }
};