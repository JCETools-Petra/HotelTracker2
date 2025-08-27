<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // app/Models/Reservation.php
    protected $fillable = [
        'property_id',
        'room_type_id', // <-- TAMBAHKAN INI
        'source',
        'final_price',
        'guest_name',
        'guest_email',
        'checkin_date',
        'checkout_date',
        'number_of_rooms',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
    ];

    /**
     * Mendapatkan pengguna yang membuat reservasi ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Anda bisa menambahkan relasi ke Property jika ada modelnya
    // public function property(): BelongsTo
    // {
    //     return $this->belongsTo(Property::class);
    // }
}