<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'animal_id', 'title', 'description', 'type',
        'species', 'breed', 'price', 'is_free', 'city', 'region',
        'photos', 'contact_phone', 'contact_email',
        'is_vaccinated', 'is_sterilized', 'is_premium',
        'is_active', 'views_count', 'expires_at',
    ];

    protected $casts = [
        'photos' => 'array',
        'is_free' => 'boolean',
        'is_vaccinated' => 'boolean',
        'is_sterilized' => 'boolean',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function animal() {
        return $this->belongsTo(Animal::class);
    }
    public function favorites() {
        return $this->morphMany(Favorite::class, 'favoritable');
    }
    public function messages() {
        return $this->hasMany(Message::class);
    }

    public function incrementViews() {
        $this->increment('views_count');
    }
}