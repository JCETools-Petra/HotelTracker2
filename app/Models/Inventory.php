<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'category',
        'unit',   // TAMBAHKAN BARIS INI
        'price',
    ];

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_amenities')->withPivot('quantity');
    }
}