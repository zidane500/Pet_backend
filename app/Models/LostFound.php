<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LostFound extends Model
{
    use HasFactory;

    protected $table = 'lost_found';

    protected $fillable = [
        'user_id', 'type', 'animal_name', 'species', 'breed',
        'color', 'description', 'last_seen_location',
        'latitude', 'longitude', 'date_lost_found',
        'photos', 'contact_phone', 'is_resolved',
    ];

    protected $casts = [
        'photos' => 'array',
        'is_resolved' => 'boolean',
        'date_lost_found' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}