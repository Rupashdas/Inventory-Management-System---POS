<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model {
    use HasFactory;
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'price',
        'unit',
        'stock',
        'low_stock_threshold',
        'img_url',
    ];

    protected $casts = [
        'stock'               => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements() {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Out of stock is its own state, not just 'below the threshold': a product
     * with none left cannot be sold at all, while a low one still can.
     */
    public function getStockStateAttribute(): string {
        if ($this->stock <= 0) {
            return 'out';
        }
        return $this->stock <= $this->low_stock_threshold ? 'low' : 'ok';
    }
}
