<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'timezone',
        'last_login_at',
        'login_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'login_token',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    // public function events()
    // {
    //     return $this->hasMany(Event::class);
    // }

    // public function generateLoginToken()
    // {
    //     $this->login_token = \Str::random(60);
    //     $this->save();
    //     return $this->login_token;
    // }
}