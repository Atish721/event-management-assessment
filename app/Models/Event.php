<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'user_id',
        'publish_date',
    ];

    protected $casts = [
        'publish_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function category()
    // {
    //     return $this->belongsTo(Category::class);
    // }

    // public function photos()
    // {
    //     return $this->hasMany(EventPhoto::class);
    // }

    // public function isPublished()
    // {
    //     return $this->publish_date->isPast();
    // }

    // public function scopePublished($query)
    // {
    //     return $query->where('publish_date', '<=',Carbon::now());
    // }

    // public function scopeWaitingForPublish($query)
    // {
    //     return $query->where('publish_date', '>',Carbon::now());
    // }
}