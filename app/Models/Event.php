<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'organization_id',
        'category_id',
        'title',
        'description',
        'date',
        'location',
        'price',
        'stock',
        'quota',
        'reserved_count',
        'sold_count',
        'status',
        'is_free',
        'poster_path',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_free' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function availableStock(): int
    {
        if ($this->quota > 0) {
            return max(0, $this->quota - $this->reserved_count - $this->sold_count);
        }

        return max(0, $this->stock);
    }

    public function averageRating(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }
}
