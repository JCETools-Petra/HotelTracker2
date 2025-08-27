<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne; // <-- Pastikan ini di-import

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'bottom_rate',
    ];

    /**
     * Definisi relasi ke Property.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * TAMBAHKAN ATAU GANTI RELASI INI
     *
     * Satu Tipe Kamar memiliki satu Aturan Harga.
     */
    public function pricingRule(): HasOne
    {
        return $this->hasOne(PricingRule::class);
    }
}