<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyIncome extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'property_id', 'user_id', 'date',
        'offline_rooms', 'online_rooms', 'ta_rooms', 'gov_rooms', 'corp_rooms', 'compliment_rooms', 'house_use_rooms', 'afiliasi_rooms',
        'offline_room_income', 'online_room_income', 'ta_income', 'gov_income', 'corp_income', 'compliment_income', 'house_use_income', 'afiliasi_room_income',
        'breakfast_income', 'lunch_income', 'dinner_income', 'mice_room_income', 'others_income',
        'total_rooms_sold', 'total_rooms_revenue', 'total_fb_revenue', 'total_revenue', 'arr', 'occupancy',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Relasi ke model Property.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    
    /**
     * Relasi ke model User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Menghitung ulang SEMUA total menggunakan data yang ada pada model.
     * Ini adalah satu-satunya sumber kebenaran untuk kalkulasi.
     */
    public function recalculateTotals($miceIncome = null)
    {
        $property = $this->property;

        // Hitung total kamar terjual
        $this->total_rooms_sold =
            ($this->offline_rooms ?? 0) + ($this->online_rooms ?? 0) + ($this->ta_rooms ?? 0) +
            ($this->gov_rooms ?? 0) + ($this->corp_rooms ?? 0) + ($this->compliment_rooms ?? 0) +
            ($this->house_use_rooms ?? 0) + ($this->afiliasi_rooms ?? 0);

        // Hitung total pendapatan kamar
        $this->total_rooms_revenue =
            ($this->offline_room_income ?? 0) + ($this->online_room_income ?? 0) + ($this->ta_income ?? 0) +
            ($this->gov_income ?? 0) + ($this->corp_income ?? 0) + ($this->compliment_income ?? 0) +
            ($this->house_use_income ?? 0) + ($this->afiliasi_room_income ?? 0);

        // Hitung total pendapatan F&B
        $this->total_fb_revenue =
            ($this->breakfast_income ?? 0) + ($this->lunch_income ?? 0) + ($this->dinner_income ?? 0);

        // Update pendapatan MICE jika dilempar dari form Admin
        if ($miceIncome !== null) {
            $this->mice_room_income = $miceIncome;
        }

        // Hitung total pendapatan keseluruhan
        $this->total_revenue = $this->total_rooms_revenue + $this->total_fb_revenue + 
                               ($this->mice_room_income ?? 0) + ($this->others_income ?? 0);

        // Hitung ARR (Average Room Rate)
        $this->arr = ($this->total_rooms_sold > 0) ? ($this->total_rooms_revenue / $this->total_rooms_sold) : 0;
        
        // ======================= PERBAIKAN LOGIKA OKUPANSI FINAL =======================
        // Gunakan kolom 'total_rooms' dari properti yang sudah Anda input manual
        $this->occupancy = ($property && $property->total_rooms > 0)
            ? ($this->total_rooms_sold / $property->total_rooms) * 100
            : 0;
        // ===============================================================================
    }
}