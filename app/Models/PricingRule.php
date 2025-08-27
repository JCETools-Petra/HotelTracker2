<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    use HasFactory;

    // Pastikan room_type_id ada di sini dan property_id sudah dihapus
    protected $fillable = [
        'room_type_id',
        'publish_rate',
        // 'publish_rate_walkin', // <-- HAPUS
        'bottom_rate',
        // 'bottom_rate_walkin', // <-- HAPUS
        'percentage_increase',
        'starting_bar',
        'bar_1', 'bar_2', 'bar_3', 'bar_4', 'bar_5',
    ];

    // Ganti relasi property() menjadi roomType()
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}