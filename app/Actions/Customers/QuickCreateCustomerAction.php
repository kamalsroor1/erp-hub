<?php

namespace App\Actions\Customers;

use App\Models\Customer;

class QuickCreateCustomerAction
{
    public function execute(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'price_tier' => $data['price_tier'] ?? 'retail',
            'address' => $data['address'] ?? null,
            'is_active' => true,
            'current_balance' => '0.000',
        ]);
    }
}
