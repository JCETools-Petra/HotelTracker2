<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Jangan lupa import ini

class Inventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'property_id', // Pastikan ini ada di $fillable
        'name',
        'category',
        'unit',
        'price',
        'description',
        'quantity',
    ];

    /**
     * ======================= TAMBAHKAN FUNGSI DI BAWAH INI =======================
     * Mendefinisikan bahwa satu Inventory dimiliki oleh (belongs to) satu Property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
    // =========================================================================
}