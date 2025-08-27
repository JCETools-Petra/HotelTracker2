<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'capacity',
        'notes',
        'property_id',
        'room_number',
        'room_type_id', // Pastikan kolom ini ada
    ];

    // Hubungan ke Property
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Hubungan ke RoomType
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    // Hubungan ke amenities (inventories)
    public function amenities()
    {
        return $this->belongsToMany(Inventory::class, 'room_amenities')->withPivot('quantity');
    }
}