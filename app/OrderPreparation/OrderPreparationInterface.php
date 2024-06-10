<?php

namespace App\OrderPreparation;

interface OrderPreparationInterface
{
    public function __construct(array $productsWithQuantity);

    public function checkIngredientsAvailability(): bool;

    public function prepareProducts(): array;

    public function getMissingIngredients(): array;
}
