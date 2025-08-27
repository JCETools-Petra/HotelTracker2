<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_number',
        'booking_date',
        'client_name',
        'event_type',
        'event_date',
        'start_time',
        'end_time',
        'participants',
        'property_id',
        'room_id',
        'person_in_charge',
        'status',
        'notes',
        'price_package_id',
        'total_price',
        'payment_status',
        'mice_category_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_date' => 'date',
        'event_date' => 'date',
    ];

    /**
     * Get the property that owns the booking.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the room for the booking.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    
    /**
     * Get the MICE category for the booking.
     */
    public function miceCategory()
    {
        return $this->belongsTo(MiceCategory::class);
    }

    /**
     * Get the function sheet for the booking.
     */
    public function functionSheet()
    {
        return $this->hasOne(FunctionSheet::class);
    }
}
