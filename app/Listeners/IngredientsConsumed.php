<?php

namespace App\Listeners;

use App\Mail\LowStockIngredient;
use Illuminate\Support\Facades\Mail;

class IngredientsConsumed
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $ingredients = $event->order->products->pluck('ingredients')->flatten();

        $ingredients->each(function ($ingredient) {
            if ($ingredient->is_notfied) {
                return;
            }

            if ($ingredient->quantity < $ingredient->notification_quantity()) {
                Mail::to('mohammed.attya25@gmail.com')->send(new LowStockIngredient($ingredient));
                $ingredient->is_notfied = true;
                $ingredient->save();
            }
        });
    }
}
