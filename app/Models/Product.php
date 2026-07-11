<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

       protected $fillable = [
        'created_by',
        'name',
        'description',
        'category',
        'price',
        'promotion_price',
        'promotion_ends_at',
        'stock_quantity',
        'photos',
        'is_active',
        'views_count',
    ];

    protected $casts = [
        'photos' => 'array',
        'price' => 'decimal:2',
        'promotion_price' => 'decimal:2',
        'promotion_ends_at' => 'datetime',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'views_count' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // ← Pratique pour valider le stock avant de créer une commande
    public function hasStock(int $quantity): bool
    {
        return $this->is_active && $this->stock_quantity >= $quantity;
    }
}