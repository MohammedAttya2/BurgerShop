<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Requests\CreateOrder;
use App\Models\Order;
use App\Models\Product;
use App\OrderPreparation\OrderPreparation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(CreateOrder $request): JsonResponse
    {
        $productsGrouped = $request->mergeProducts();
        $productIds = collect($productsGrouped)->pluck('product_id')->unique();
        $products = Product::with('ingredients')->whereIn('id', $productIds)->get();
        $products->each(function ($product) use (&$productsGrouped) {
            $productsGrouped[$product->id]['product'] = $product;
        });

        $orderPreparation = new OrderPreparation($productsGrouped);
        if (! $orderPreparation->checkIngredientsAvailability()) {
            $message = 'The following ingredients are not available: '.implode(', ', $orderPreparation->getMissingIngredients());

            return response()->json(['message' => $message], 400);
        }

        $order = Order::make();
        DB::Transaction(function () use ($orderPreparation, $order) {
            $order->save();
            $preparedProducts = $orderPreparation->prepareProducts();
            $order->addProducts($preparedProducts);
            OrderCreated::dispatch($order);
        });

        return response()->json([
            'message' => 'Order created successfully',
            'order_id' => $order->id,
        ], 201);
    }
}
