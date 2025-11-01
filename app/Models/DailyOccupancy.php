<?php

namespace App\Models;

// HAPUS 'use App\Events\OccupancyUpdated;' DARI SINI
// use App\Events\OccupancyUpdated; 
use App\Services\ReservationPriceService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyOccupancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'date',
        'occupied_rooms',
        'reservasi_ota',
        'reservasi_properti',
    ];

    // ==========================================================
    // >> AWAL PERUBAHAN <<
    // ==========================================================

    // Array $dispatchesEvents DIHAPUS DARI SINI
    // protected $dispatchesEvents = [
    //     'saved' => OccupancyUpdated::class,
    // ];

    // ==========================================================
    // >> AKHIR PERUBAHAN <<
    // ==========================================================

    protected $casts = [
        'date' => 'date',
        'occupied_rooms' => 'integer',
        'reservasi_ota' => 'integer',
        'reservasi_properti' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    // ==========================================================
    // >> AWAL PERUBAHAN <<
    // ==========================================================

    // Method boot() DIHAPUS DARI SINI
    // protected static function boot()
    // {
    //     parent::boot();
    //     ...
    // }

    // ==========================================================
    // >> AKHIR PERUBAHAN <<
    // ==========================================================


    /**
     * Mendapatkan persentase okupansi.
     */
    public function getOccupancyPercentageAttribute(): float
    {
        if ($this->property->total_rooms > 0) {
            return ($this->occupied_rooms / $this->property->total_rooms) * 100;
        }
        return 0;
    }

    /**
     * Mendapatkan harga BAR yang aktif untuk tanggal ini.
     */
    public function getActiveBarPriceAttribute(): array
    {
        if (!$this->property) {
            return ['level' => 'N/A', 'price' => 0];
        }

        // Gunakan service untuk kalkulasi
        $priceService = app(ReservationPriceService::class);
        
        // Panggil method yang sudah ada di service
        return $priceService->getActiveBarPriceForDate($this->property, $this->date);
    }
}