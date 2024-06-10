<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
// use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $beef = Ingredient::factory()->create([
            'name' => 'Beef',
            'stock' => 20000,
            'initial_stock' => 20000,
        ]);

        $cheese = Ingredient::factory()->create([
            'name' => 'Cheese',
            'stock' => 5000,
            'initial_stock' => 5000,
        ]);

        $onion = Ingredient::factory()->create([
            'name' => 'Onion',
            'stock' => 1000,
            'initial_stock' => 1000,
        ]);

        $burger = Product::factory()->create([
            'name' => 'Burger',
        ]);

        $burger->ingredients()->attach($beef, ['quantity' => 150]);
        $burger->ingredients()->attach($cheese, ['quantity' => 30]);
        $burger->ingredients()->attach($onion, ['quantity' => 20]);
    }
}
