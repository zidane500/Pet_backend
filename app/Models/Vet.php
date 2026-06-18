<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'clinic_name', 'doctor_name', 'speciality',
        'phone', 'email', 'address', 'city', 'region',
        'latitude', 'longitude', 'opening_hours', 'services',
        'photo', 'is_verified', 'is_active', 'rating', 'reviews_count',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'services' => 'array',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'rating' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function reviews() {
        return $this->morphMany(Review::class, 'reviewable');
    }
    public function favorites() {
        return $this->morphMany(Favorite::class, 'favoritable');
    }
}