<?php

namespace App\OrderPreparation;

use App\Models\Product;

interface ProductPreparationInterface
{
    public function __construct(Product $product, int $quantity);

    public function checkIngredientsAvailability(): bool;

    public function prepareProduct(): bool;
}
