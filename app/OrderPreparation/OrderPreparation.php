<?php

namespace App\OrderPreparation;

class OrderPreparation implements OrderPreparationInterface
{
    protected array $missingIngredients = [];

    public function __construct(
        public array $productsWithQuantity
    ) {
    }

    public function checkIngredientsAvailability(): bool
    {
        $isAvailable = true;
        foreach ($this->productsWithQuantity as $productWithQuantity) {
            $productPreparation = new ProductPreparation($productWithQuantity['product'], $productWithQuantity['quantity']);
            if (! $productPreparation->checkIngredientsAvailability()) {
                $this->missingIngredients = array_merge($this->missingIngredients, $productPreparation->getMissingIngredients());
                $isAvailable = false;
            }
        }

        return $isAvailable;
    }

    public function prepareProducts(): array
    {
        $preparedProducts = [];
        foreach ($this->productsWithQuantity as $productWithQuantity) {
            $productPreparation = new ProductPreparation($productWithQuantity['product'], $productWithQuantity['quantity']);
            $productPreparation->prepareProduct();
            $preparedProducts[$productWithQuantity['product']->id] = ['quantity' => $productWithQuantity['quantity']];
        }

        return $preparedProducts;
    }

    public function getMissingIngredients(): array
    {
        return $this->missingIngredients;
    }
}
