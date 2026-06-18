<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'plan', 'status', 'price_paid',
        'payment_method', 'transaction_id', 'starts_at', 'expires_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'price_paid' => 'decimal:2',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool {
        return $this->status === 'active' && $this->expires_at?->isFuture();
    }
}