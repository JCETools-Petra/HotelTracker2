<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // app/Models/Inventory.php

    protected $fillable = [
        'item_code',
        'property_id',
        'name',
        'specification',
        'category_id',
        'stock',
        'minimum_standard_quantity', // <-- TAMBAHKAN INI
        'unit',
        'unit_price',
        'condition',
        'purchase_date',             // <-- TAMBAHKAN INI
    ];

    /**
     * Mendapatkan kategori dari inventaris.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Mendapatkan transaksi dari inventaris.
     */
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
    
    /**
     * Mendapatkan properti dari inventaris.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}