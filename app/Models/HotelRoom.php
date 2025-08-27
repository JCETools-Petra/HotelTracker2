<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hotel_rooms';

    protected $fillable = [
        'property_id',
        'room_number',
        'room_type_id',
        'capacity',
        'notes',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Inventory::class, 'room_amenities', 'room_id', 'inventory_id')
            ->withPivot('quantity');
    }
}
