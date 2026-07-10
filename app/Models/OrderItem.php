<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'unit_price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // ← nullable : le produit peut avoir été supprimé depuis, mais on
    // garde product_name/unit_price en copie dans cette table donc
    // l'historique de commande reste lisible même dans ce cas.
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}