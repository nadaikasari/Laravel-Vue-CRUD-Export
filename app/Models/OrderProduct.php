<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_name',
        'qty',
        'price',
        'subtotal',
    ];

    /**
     * Get the order that owns the order product.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
