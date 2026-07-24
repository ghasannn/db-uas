<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'owner_user_id',
        'logo',
        'description',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members()
    {
        return $this->hasMany(User::class, 'organization_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'organization_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'organization_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'organization_id');
    }

    public function averageRating()
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }
}
