<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
protected $fillable = ['name', 'slug'];

// Menandakan atribut: 1 Kategori dapat memiliki banyak list Event

public function events()
{
return $this->hasMany(Event::class);
}
}