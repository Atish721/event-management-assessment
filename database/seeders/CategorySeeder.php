<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
  
    public function run()
    {
        $technology = Category::create(['name' => 'Technology']);
        $business = Category::create(['name' => 'Business']);
        $sports = Category::create(['name' => 'Sports']);
        $entertainment = Category::create(['name' => 'Entertainment']);
        $education = Category::create(['name' => 'Education']);

     
        Category::create([
            'name' => 'Programming',
            'parent_id' => $technology->id
        ]);
        Category::create([
            'name' => 'Artificial Intelligence',
            'parent_id' => $technology->id
        ]);
        Category::create([
            'name' => 'Web Development',
            'parent_id' => $technology->id
        ]);

    
        Category::create([
            'name' => 'Startups',
            'parent_id' => $business->id
        ]);
        Category::create([
            'name' => 'Finance',
            'parent_id' => $business->id
        ]);

    
        Category::create([
            'name' => 'Football',
            'parent_id' => $sports->id
        ]);
        Category::create([
            'name' => 'Basketball',
            'parent_id' => $sports->id
        ]);

    
        $programming = Category::where('name', 'Programming')->first();
        Category::create([
            'name' => 'Laravel',
            'parent_id' => $programming->id
        ]);
        Category::create([
            'name' => 'React',
            'parent_id' => $programming->id
        ]);

        $this->command->info('Categories seeded successfully with nested structure!');
    }
}