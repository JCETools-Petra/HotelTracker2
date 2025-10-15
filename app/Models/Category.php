<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'category_code',
    ];

    /**
     * Mendapatkan semua item inventaris dalam kategori ini.
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}