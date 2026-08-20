<?php

namespace App\Actions\Customers;

use App\Services\CustomerPricingHelper;

class GetCustomerLastSoldPriceAction
{
    public function __construct(
        protected CustomerPricingHelper $pricingHelper
    ) {}

    public function execute(int $customerId, int $itemId, ?int $storeId = null): ?array
    {
        return $this->pricingHelper->getLastSoldPrice($customerId, $itemId, $storeId);
    }
}
