<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_has_many_products(): void
    {
        $order = new Order();
        $order->save();

        $product = new Product();
        $product->name = 'Test Product';
        $product->save();

        $order->products()->attach($product->id, ['quantity' => 2]);

        $this->assertEquals(1, $order->products->count());
    }
}
