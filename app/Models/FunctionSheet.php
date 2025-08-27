<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'beo_number',
        'contact_phone',
        'dealed_by',
        'room_setup',
        'price_package_id', // <-- UBAH INI
        'event_segments',
        'menu_details',
        'equipment_details',
        'department_notes',
        'notes',
    ];

    protected $casts = [
        'event_segments' => 'array',
        'menu_details' => 'array',
        'equipment_details' => 'array',
        'department_notes' => 'array',
        // 'billing_details' => 'array', HAPUS INI
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
    public function pricePackage()
    {
        return $this->belongsTo(PricePackage::class);
    }
    public function miceCategory()
{
    return $this->belongsTo(MiceCategory::class);
}
}