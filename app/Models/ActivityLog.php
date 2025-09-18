<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'property_id', // <-- SUDAH ADA, BAGUS!
        'action',
        'description',
        'loggable_id',
        'loggable_type',
        'changes',
        'ip_address',
        'user_agent'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'changes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * TAMBAHKAN FUNGSI INI
     * Mendefinisikan relasi ke model Property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}