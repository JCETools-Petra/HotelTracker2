<?php

namespace App\Models; // Pastikan tidak ada baris kosong di atas ini setelah <?php

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Untuk casting dan mutator tanggal jika diperlukan

class RevenueTarget extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'revenue_targets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'property_id',
        'month_year',
        'target_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'month_year' => 'date:Y-m-d', // Casting ke objek Carbon saat diakses, format Y-m-d
        'target_amount' => 'decimal:2', // Casting ke float dengan 2 angka desimal
    ];

    /**
     * Get the property that owns the revenue target.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Scope a query to only include targets for a specific month and year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int $year
     * @param  int $month
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMonthYear($query, $year, $month)
    {
        // Membuat tanggal awal dan akhir bulan dari tahun dan bulan yang diberikan
        // $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        // Meskipun kita menyimpan 'YYYY-MM-01', query whereMonth dan whereYear lebih aman
        return $query->whereYear('month_year', $year)
                     ->whereMonth('month_year', $month);
    }

    /**
     * Mutator untuk memastikan month_year selalu disimpan sebagai tanggal pertama bulan.
     * Ini akan dieksekusi setiap kali Anda mencoba set atribut 'month_year'.
     */
    public function setMonthYearAttribute($value)
    {
        // Mengubah input (misalnya '2025-05' atau '2025-05-15') menjadi '2025-05-01'
        $this->attributes['month_year'] = Carbon::parse($value)->startOfMonth()->toDateString();
    }
}
