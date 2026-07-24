<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'organization_id',
        'provider',
        'provider_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function ownedOrganization()
    {
        return $this->hasOne(Organization::class, 'owner_user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isSuperadmin(): bool
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    public function isOrganizer(): bool
    {
        return in_array($this->role, ['organizer_owner', 'organizer_staff']);
    }

    public function isBuyer(): bool
    {
        return $this->role === 'buyer' || $this->role === 'user';
    }
}
