<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Breeder extends Model
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
        'is_certified',
        'speciality',
        'years_experience',
        'animals_sold_total',
        'rating',
        'reviews_count',
        'description',
        'is_active',
    ];

    protected $casts = [
        'verified'           => 'boolean',
        'is_certified'       => 'boolean',
        'is_active'          => 'boolean',
        'years_experience'   => 'integer',
        'animals_sold_total' => 'integer',
        'reviews_count'      => 'integer',
        'rating'             => 'float',
    ];

    // ── Relations ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'target_id')
                    ->where('target_type', 'breeder');
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