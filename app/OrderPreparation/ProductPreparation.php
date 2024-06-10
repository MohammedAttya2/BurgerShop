<?php

namespace App\OrderPreparation;

use App\Models\Product;

class ProductPreparation implements ProductPreparationInterface
{
    protected array $missingIngredients = [];

    public function __construct(
        protected Product $product,
        protected int $quantity
    ) {
    }

    public function checkIngredientsAvailability(): bool
    {
        $isAvalable = true;
        foreach ($this->product->ingredients as $ingredient) {
            if ($ingredient->stock < ($ingredient->pivot->quantity * $this->quantity)) {
                $isAvalable = false;
                $this->missingIngredients[] = $ingredient->name;
            }
        }

        return $isAvalable;
    }

    public function prepareProduct(): bool
    {
        foreach ($this->product->ingredients as $ingredient) {
            $requiredQuantity = $ingredient->pivot->quantity * $this->quantity;
            $ingredient->stock -= $requiredQuantity;
            $ingredient->save();
        }

        return true;
    }

    public function getMissingIngredients(): array
    {
        return $this->missingIngredients;
    }
}
