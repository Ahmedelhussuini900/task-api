<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => 'sometimes|required|integer|exists:warehouses,id',
            'inventory_item_id' => 'sometimes|required|integer|exists:inventory_items,id',
            'quantity' => 'sometimes|required|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'warehouse_id.exists' => 'The selected warehouse does not exist.',
            'inventory_item_id.exists' => 'The selected inventory item does not exist.',
            'quantity.min' => 'The quantity must be at least 0.',
        ];
    }
}
