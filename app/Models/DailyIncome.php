<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyIncome extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'property_id',
        'user_id',
        'date',
        
        // Kolom jumlah kamar
        'offline_rooms',
        'online_rooms',
        'ta_rooms',
        'gov_rooms',
        'corp_rooms',
        'compliment_rooms',
        'house_use_rooms',
        'afiliasi_rooms',

        // Kolom pendapatan
        'offline_room_income',
        'online_room_income',
        'ta_income',
        'gov_income',
        'corp_income',
        'compliment_income',
        'house_use_income',
        'afiliasi_room_income',
        'breakfast_income',
        'lunch_income',
        'dinner_income',
        'mice_room_income',
        'others_income',

        // Kolom kalkulasi
        'total_rooms_sold',
        'total_rooms_revenue',
        'total_fb_revenue',
        'total_revenue',
        'arr',
        'occupancy',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Accessor untuk menghitung total F&B secara otomatis.
     */
    public function getFbIncomeAttribute(): float
    {
        return $this->breakfast_income + $this->lunch_income + $this->dinner_income;
    }

    /**
     * Mendapatkan properti yang memiliki pendapatan ini.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    
    /**
     * Mendapatkan pengguna yang mencatat pendapatan ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Recalculates and updates all total fields for this daily income record.
     * This method centralizes the business logic for income calculation.
     */
    public function recalculateTotals()
    {
        $property = $this->property;

        // Calculate total rooms sold
        $total_rooms_sold =
            ($this->offline_rooms ?? 0) + ($this->online_rooms ?? 0) +
            ($this->ta_rooms ?? 0) + ($this->gov_rooms ?? 0) +
            ($this->corp_rooms ?? 0) + ($this->compliment_rooms ?? 0) +
            ($this->house_use_rooms ?? 0) + ($this->afiliasi_rooms ?? 0);

        // Calculate total rooms revenue
        $total_rooms_revenue =
            ($this->offline_room_income ?? 0) + ($this->online_room_income ?? 0) +
            ($this->ta_income ?? 0) + ($this->gov_income ?? 0) +
            ($this->corp_income ?? 0) + ($this->compliment_income ?? 0) +
            ($this->house_use_income ?? 0) + ($this->afiliasi_room_income ?? 0);

        // Calculate total F&B revenue
        $total_fb_revenue =
            ($this->breakfast_income ?? 0) + ($this->lunch_income ?? 0) +
            ($this->dinner_income ?? 0);

        // Calculate total overall revenue
        $total_revenue = $total_rooms_revenue + $total_fb_revenue + 
                         ($this->mice_room_income ?? 0) + ($this->others_income ?? 0);

        // Calculate ARR and Occupancy
        $arr = ($total_rooms_sold > 0) ? ($total_rooms_revenue / $total_rooms_sold) : 0;
        $occupancy = ($property && $property->total_rooms > 0) ? ($total_rooms_sold / $property->total_rooms) * 100 : 0;

        // Update the model's attributes
        $this->total_rooms_sold = $total_rooms_sold;
        $this->total_rooms_revenue = $total_rooms_revenue;
        $this->total_fb_revenue = $total_fb_revenue;
        $this->total_revenue = $total_revenue;
        $this->arr = $arr;
        $this->occupancy = $occupancy;
        
        // Save all changes to the database
        $this->save();
    }
}