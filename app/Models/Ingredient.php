<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected function stockInKg(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['stock'] / 1000,
        );
    }

    protected function initialStockInKg(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['initial_stock'] / 1000,
        );
    }

    public function hasCriticalStock(): bool
    {
        return $this->stock < ($this->initial_stock / 2);
    }

    public function notification_quantity(): float
    {
        return $this->initial_stock / 2;
    }
}
