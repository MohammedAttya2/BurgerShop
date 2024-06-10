<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrder extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'products.required' => 'The products field is required.',
            'products.array' => 'The products must be an array.',
            'products.*.product_id.required' => 'The product_id field is required.',
            'products.*.product_id.integer' => 'The product_id must be an integer.',
            'products.*.product_id.exists' => 'The selected product_id is invalid.',
            'products.*.quantity.required' => 'The quantity field is required.',
            'products.*.quantity.integer' => 'The quantity must be an integer.',
            'products.*.quantity.min' => 'The quantity must be at least 1.',
        ];
    }

    public function mergeProducts(): array
    {
        $productsGrouped = [];
        foreach ($this->products as $product) {
            if (empty($productsGrouped[$product['product_id']])) {
                $productsGrouped[$product['product_id']] = [
                    'quantity' => 0,
                    'product_id' => $product['product_id'],
                ];
            }
            $productsGrouped[$product['product_id']]['quantity'] += $product['quantity'];
        }

        return $productsGrouped;
    }
}
