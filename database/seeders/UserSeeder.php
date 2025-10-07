<?php


namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
   
    
    public function run()
    {
        
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'timezone' => 'UTC',
            'last_login_at' => null,
            'login_token' => Str::random(60),
            'remember_token' => Str::random(10),
        ]);

        
        User::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'timezone' => 'America/New_York',
            'last_login_at' => now()->subDays(2),
            'login_token' => Str::random(60),
            'remember_token' => Str::random(10),
        ]);

        
        User::create([
            'username' => 'eventmanager',
            'password' => Hash::make('event123'),
            'timezone' => 'Europe/London',
            'last_login_at' => now()->subHours(5),
            'login_token' => Str::random(60),
            'remember_token' => Str::random(10),
        ]);

    
        
    }
}