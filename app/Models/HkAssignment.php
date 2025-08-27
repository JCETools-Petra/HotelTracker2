<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HkAssignment extends Model
{
    use HasFactory;

    protected $table = 'hk_assignments';

    protected $fillable = [
        'user_id',
        'room_id',
    ];

    /**
     * Relasi ke model User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke model HotelRoom.
     * Sebuah penugasan (assignment) terkait dengan satu kamar hotel.
     */
    public function room()
    {
        return $this->belongsTo(HotelRoom::class, 'room_id');
    }
}