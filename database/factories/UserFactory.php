<?php


namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $timezones = ['UTC', 'America/New_York', 'Europe/London', 'Asia/Kolkata', 'Australia/Sydney'];
        
        return [
            'username' => $this->faker->unique()->userName(),
            'password' => Hash::make('password'),
            'timezone' => $this->faker->randomElement($timezones),
            'last_login_at' => $this->faker->optional()->dateTimeThisMonth(),
            'login_token' => Str::random(60),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}