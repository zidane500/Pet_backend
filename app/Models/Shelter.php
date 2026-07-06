<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shelter extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'tagline',
        'address',
        'city',
        'phone',
        'email',
        'website',
        'logo',
        'cover_image',
        'verified',
        'is_nonprofit',
        'capacity',
        'current_animals',
        'volunteers_count',
        'animals_helped_total',
        'rating',
        'reviews_count',
        'description',
        'is_active',
    ];

    protected $casts = [
        'verified'             => 'boolean',
        'is_nonprofit'         => 'boolean',
        'is_active'            => 'boolean',
        'capacity'             => 'integer',
        'current_animals'      => 'integer',
        'volunteers_count'     => 'integer',
        'animals_helped_total' => 'integer',
        'reviews_count'        => 'integer',
        'rating'               => 'float',
    ];

    // ── Relations ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'target_id')
                    ->where('target_type', 'shelter');
    }

    // ── Accesseurs ─────────────────────────────────────────────

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) return null;
        if (str_starts_with($this->logo, 'http')) return $this->logo;
        return config('app.url') . '/storage/' . $this->logo;
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) return null;
        if (str_starts_with($this->cover_image, 'http')) return $this->cover_image;
        return config('app.url') . '/storage/' . $this->cover_image;
    }
}