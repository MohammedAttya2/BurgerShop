<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Mail\LowStockIngredient;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class OrderPlacedTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_order_placed_returns_created_status_code(): void
    {
        $this->seed();

        $response = $this->postJson('/orders', [
            'products' => [[
                'product_id' => 1,
                'quantity' => 1,
            ]],
        ]);

        $response->assertStatus(201);
    }

    public function test_order_placed_with_missing_product_produces_validation_error(): void
    {
        $response = $this->postJson('/orders', [
            'products' => [[
                'product_id' => 1,
                'quantity' => 1,
            ]],
        ]);

        $response->assertStatus(422); // 422 is the status code for validation error
    }

    public function test_order_placed_with_missing_ingredients_produces_error(): void
    {
        $this->seed();

        $response = $this->postJson('/orders', [
            'products' => [[
                'product_id' => 1,
                'quantity' => 2000,
            ]],
        ]);

        $response->assertStatus(400); // 400 is the status code for bad request And I am using it for insufficient ingredients
    }

    public function test_successful_order_could_be_placed_with_multiple_products(): void
    {
        $this->seed();

        $response = $this->postJson('/orders', [
            'products' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                ],
                [
                    'product_id' => 1,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(201);
    }

    public function test_order_placed_with_multiple_products_with_missing_ingredients_produces_error(): void
    {
        $this->seed();

        $response = $this->postJson('/orders', [
            'products' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                ],
                [
                    'product_id' => 1,
                    'quantity' => 2000,
                ],
            ],
        ]);

        $response->assertStatus(400);
    }

    public function test_failed_orders_dont_consume_ingredients(): void
    {
        $this->seed();

        $response = $this->postJson('/orders', [
            'products' => [[
                'product_id' => 1,
                'quantity' => 2000,
            ]],
        ]);

        $response->assertStatus(400);
        $ingredient1 = Ingredient::find(1);
        $ingredient2 = Ingredient::find(2);
        $ingredient3 = Ingredient::find(3);

        assertEquals($ingredient1->stock, $ingredient1->initial_stock);
        assertEquals($ingredient2->stock, $ingredient2->initial_stock);
        assertEquals($ingredient3->stock, $ingredient3->initial_stock);
    }

    public function test_it_returs_error_message_for_insufficient_ingredients(): void
    {
        $this->seed();

        $response = $this->postJson('/orders', [
            'products' => [[
                'product_id' => 1,
                'quantity' => 2000,
            ]],
        ]);

        $ingredients = Ingredient::whereIn('id', [1, 2, 3])->get('name')->pluck('name')->toArray();

        $response->assertJson(['message' => 'The following ingredients are not available: '.implode(', ', $ingredients)]);
    }

    public function test_it_dispatches_order_created_event(): void
    {
        $this->seed();
        Event::fake([OrderCreated::class]);

        $response = $this->postJson('/orders', [
            'products' => [[
                'product_id' => 1,
                'quantity' => 1,
            ]],
        ]);

        Event::assertDispatched(OrderCreated::class);
    }

    public function test_it_sends_an_email_for_each_ingredient_with_critical_stock(): void
    {
        Mail::fake();
        $this->seed();
        $ingredient = Ingredient::find(1);
        $ingredient->stock = $ingredient->initial_stock / 2 - 1;
        $ingredient->save();

        $ingredient = Ingredient::find(2);
        $ingredient->stock = $ingredient->initial_stock / 2 - 1;
        $ingredient->save();

        $ingredient = Ingredient::find(3);
        $ingredient->stock = $ingredient->initial_stock / 2 - 1;
        $ingredient->save();

        // Assert that no mailable were sent...
        Mail::assertNothingSent();

        // Assert that a mailable was sent...

        $response = $this->postJson('/orders', [
            'products' => [[
                'product_id' => 1,
                'quantity' => 1,
            ]],
        ]);

        Mail::assertSent(LowStockIngredient::class, 3);
    }

    public function test_it_sends_only_one_email_for_each_ingredient_with_critical_stock(): void
    {
        Mail::fake();
        $this->seed();
        $ingredient = Ingredient::find(1);
        $ingredient->stock = $ingredient->initial_stock / 2 - 1;
        $ingredient->save();

        // Assert that no mailable were sent...
        Mail::assertNothingSent();

        // Assert that a mailable was sent...
        $response = $this->postJson('/orders', [
            'products' => [[
                'product_id' => 1,
                'quantity' => 1,
            ]],
        ]);

        Mail::assertSent(LowStockIngredient::class, 3);

        $response = $this->postJson('/orders', [
            'products' => [[
                'product_id' => 1,
                'quantity' => 1,
            ]],
        ]);

        Mail::assertSent(LowStockIngredient::class, 3);
    }

    public function test_it_consumes_the_correct_product_ingredients_quantity(): void
    {
        $this->seed();

        $response = $this->postJson('/orders', [
            'products' => [[
                'product_id' => 1,
                'quantity' => 1,
            ]],
        ]);

        $ingredient1 = Ingredient::find(1);
        $ingredient2 = Ingredient::find(2);
        $ingredient3 = Ingredient::find(3);

        $product = Product::with('ingredients')->find(1);
        foreach ($product->ingredients as $ingredient) {
            $requiredQuantity = $ingredient->pivot->quantity;
            assertEquals($ingredient->stock, $ingredient->initial_stock - $requiredQuantity);
        }
    }

    public function test_it_returns_order_details_for_successful_order(): void
    {
        $this->seed();

        $response = $this->postJson('/orders', [
            'products' => [[
                'product_id' => 1,
                'quantity' => 1,
            ]],
        ]);

        $response->assertJson([
            'message' => 'Order created successfully',
            'order_id' => 1,
        ]);
    }
}
