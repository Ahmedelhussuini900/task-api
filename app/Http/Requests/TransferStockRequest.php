<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferStockRequest extends FormRequest
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
            'inventory_item_id' => 'required|integer|exists:inventory_items,id',
            'from_warehouse_id' => 'required|integer|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|integer|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'inventory_item_id.required' => 'The inventory item is required.',
            'inventory_item_id.exists' => 'The selected inventory item does not exist.',
            'from_warehouse_id.required' => 'The source warehouse is required.',
            'from_warehouse_id.exists' => 'The selected source warehouse does not exist.',
            'from_warehouse_id.different' => 'The source and destination warehouses must be different.',
            'to_warehouse_id.required' => 'The destination warehouse is required.',
            'to_warehouse_id.exists' => 'The selected destination warehouse does not exist.',
            'to_warehouse_id.different' => 'The source and destination warehouses must be different.',
            'quantity.required' => 'The quantity is required.',
            'quantity.min' => 'The quantity must be at least 1.',
        ];
    }
}
