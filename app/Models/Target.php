<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'year',
        'month',
        'target_amount',
        // 'income_category', // Aktifkan jika Anda menambahkannya di migrasi
    ];

    /**
     * Mendapatkan properti yang memiliki target ini.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    // Anda bisa menambahkan accessor untuk nama bulan jika diperlukan
    public function getMonthNameAttribute()
    {
        if ($this->month) {
            return \Carbon\Carbon::create()->month($this->month)->isoFormat('MMMM');
        }
        return null;
    }
}