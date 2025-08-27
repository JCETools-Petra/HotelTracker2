<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyOccupancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'date',
        'occupied_rooms',
    ];
}