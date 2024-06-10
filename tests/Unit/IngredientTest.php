<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    public function test_ingredient_has_stock_in_kg_attr(): void
    {
        $stockInGrams = 1000;
        $stockInKg = 1;
        $ingredient = new Ingredient();
        $ingredient->stock = $stockInGrams;
        $ingredient->initial_stock = 2000;
        $ingredient->name = 'Test Ingredient';
        $ingredient->save();

        $this->assertEquals($stockInKg, $ingredient->stockInKg);
    }

    public function test_ingredient_has_initial_stock_in_kg_attr(): void
    {
        $stockInGrams = 1000;
        $initialStock = 2000;
        $initialStockInKg = 2;
        $ingredient = new Ingredient();
        $ingredient->stock = $stockInGrams;
        $ingredient->initial_stock = $initialStock;
        $ingredient->name = 'Test Ingredient';
        $ingredient->save();

        $this->assertEquals($initialStockInKg, $ingredient->initialStockInKg);
    }

    public function test_ingredient_has_critical_stock_less_than_half_initial_stock(): void
    {
        $initialStock = 2000;
        $stock = ($initialStock / 2) - 1;
        $ingredient = new Ingredient();
        $ingredient->stock = $stock;
        $ingredient->initial_stock = $initialStock;
        $ingredient->name = 'Test Ingredient';
        $ingredient->save();

        $this->assertTrue($ingredient->hasCriticalStock());
    }

    public function test_ingredient_has_notification_quantity_less_than_half_initial_stock(): void
    {
        $initialStock = 2000;
        $stock = 2000;
        $ingredient = new Ingredient();
        $ingredient->stock = $stock;
        $ingredient->initial_stock = $initialStock;
        $ingredient->name = 'Test Ingredient';
        $ingredient->save();

        $notificationQuantity = $initialStock / 2;

        $this->assertEquals($notificationQuantity, $ingredient->notification_quantity());
    }
}
