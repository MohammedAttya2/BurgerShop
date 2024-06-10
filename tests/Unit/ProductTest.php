<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_has_many_ingredients(): void
    {
        $product = new Product();
        $product->name = 'Test Product';
        $product->save();

        $ingredient = new Ingredient();
        $ingredient->name = 'Test Ingredient';
        $ingredient->stock = 1000;
        $ingredient->initial_stock = 2000;
        $ingredient->save();

        $ingredient2 = new Ingredient();
        $ingredient2->name = 'Test Ingredient2';
        $ingredient2->stock = 1000;
        $ingredient2->initial_stock = 2000;
        $ingredient2->save();

        $product->ingredients()->attach($ingredient->id, ['quantity' => 2]);
        $product->ingredients()->attach($ingredient2->id, ['quantity' => 21]);

        $this->assertEquals(2, $product->ingredients->count());
    }
}
