<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
// HAPUS 'use App\Events\OccupancyUpdated;' DARI SINI

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'chart_color',
        'address',
        'phone_number',
        'total_rooms', 
        'bar_1',
        'bar_2',
        'bar_3',
        'bar_4',
        'bar_5',
        'bar_active', // <-- Kolom 'bar_active' tetap ada di $fillable
    ];
    
    public function pricingRule(): HasOne
    {
        return $this->hasOne(PricingRule::class);
    }
    
    public function manager(): HasOne
    {
        return $this->hasOne(User::class);
    }
    
    public function dailyIncomes(): HasMany
    {
        return $this->hasMany(DailyIncome::class);
    }
    
    public function dailyOccupancies(): HasMany
    {
        return $this->hasMany(DailyOccupancy::class);
    }
    
    // Hubungan ke ruangan MICE
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    // Hubungan ke kamar hotel
    public function hotelRooms()
    {
        return $this->hasMany(HotelRoom::class);
    }
    
    public function incomes()
    {
        return $this->hasMany(Income::class);
    }
    
    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    // ==========================================================
    // METHOD 'booted()' YANG SEBELUMNYA ADA DI SINI, SEKARANG DIHAPUS
    // ==========================================================
}