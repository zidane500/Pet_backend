<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone',
        'city', 'region', 'avatar', 'bio', 'plan',
        'is_verified', 'is_active', 'locale',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function listings() {
        return $this->hasMany(Listing::class);
    }
    public function animals() {
        return $this->hasMany(Animal::class);
    }
    public function messages() {
        return $this->hasMany(Message::class, 'sender_id');
    }
    public function favorites() {
        return $this->hasMany(Favorite::class);
    }
    public function subscription() {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }
    public function vet() {
        return $this->hasOne(Vet::class);
    }
    public function petStore() {
        return $this->hasOne(PetStore::class);
    }
    public function lostFoundReports() {
    return $this->hasMany(LostFound::class);
}
}