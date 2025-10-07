<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'parent_id'];

    // public function parent()
    // {
    //     return $this->belongsTo(Category::class, 'parent_id');
    // }

    // public function children()
    // {
    //     return $this->hasMany(Category::class, 'parent_id');
    // }

    // public function events()
    // {
    //     return $this->hasMany(Event::class);
    // }

    // public function getNestedNameAttribute()
    // {
    //     $name = $this->name;
    //     $parent = $this->parent;
        
    //     while ($parent) {
    //         $name = $parent->name . ' > ' . $name;
    //         $parent = $parent->parent;
    //     }
        
    //     return $name;
    // }
}