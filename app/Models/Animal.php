<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'species', 'breed', 'gender',
        'age_months', 'description', 'photos', 'is_vaccinated',
        'is_sterilized', 'is_available', 'price', 'city', 'region',
    ];

    protected $casts = [
        'photos' => 'array',
        'is_vaccinated' => 'boolean',
        'is_sterilized' => 'boolean',
        'is_available' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function listings() {
        return $this->hasMany(Listing::class);
    }
}