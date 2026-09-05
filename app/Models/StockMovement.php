<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'invoice_id',
        'reason',
        'quantity',
        'balance_after',
        'note',
    ];

    protected $casts = [
        'quantity'      => 'integer',
        'balance_after' => 'integer',
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }
}
