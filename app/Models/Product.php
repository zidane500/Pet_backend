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

    // ← IMPORTANT : "effective_price" est calculé UNE SEULE FOIS ici et
    // ajouté automatiquement à chaque réponse JSON du modèle (catalogue,
    // panier, commande...). Toute l'app (front ET back) doit se fier à
    // cette seule valeur au lieu de recalculer "est-ce que la promo est
    // active ?" à plusieurs endroits différents — c'est exactement ce
    // genre de duplication qui causait le bug du panier qui restait sur
    // l'ancien prix.
    protected $appends = ['effective_price'];

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

    /**
     * Une promotion est active si un promotion_price valide est défini
     * ET (pas de date de fin, OU la date de fin n'est pas encore passée).
     * Même règle que côté frontend (BoutiquePage.tsx::isPromotionActive)
     * — à garder synchronisée si l'une des deux change.
     */
    public function isPromotionActive(): bool
    {
        if (!$this->promotion_price || (float) $this->promotion_price <= 0) {
            return false;
        }

        if (!$this->promotion_ends_at) {
            return true;
        }

        return $this->promotion_ends_at->isFuture();
    }

    /**
     * Le prix RÉELLEMENT payé par le client en ce moment : le prix promo
     * s'il est actif, sinon le prix normal. C'est CETTE valeur que
     * OrderController doit utiliser pour facturer — jamais `price`
     * directement.
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->isPromotionActive()
            ? (float) $this->promotion_price
            : (float) $this->price;
    }
}