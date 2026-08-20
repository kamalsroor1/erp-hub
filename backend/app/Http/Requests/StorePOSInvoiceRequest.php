<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePOSInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pos.access') ?? true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'store_id' => 'required|exists:stores,id',
            'invoice_date' => 'required|date',
            'payment_type' => 'required|in:cash,credit,partial',
            'payment_method' => 'nullable|string|in:cash,instapay,e_wallet,visa,bank_transfer',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'additional_expenses' => 'nullable|array',
            'additional_expenses.*.title' => 'nullable|string|max:150',
            'additional_expenses.*.amount' => 'nullable|numeric|min:0',
        ];
    }
}
