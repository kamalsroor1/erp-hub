<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Supplier;
use App\Services\ExportService;

class ExportController extends Controller
{
    public function exportCustomerStatement($id, ExportService $exportService)
    {
        $customer = Customer::findOrFail($id);
        return $exportService->exportCustomerStatement($customer);
    }

    public function exportSupplierStatement($id, ExportService $exportService)
    {
        $supplier = Supplier::findOrFail($id);
        return $exportService->exportSupplierStatement($supplier);
    }

    public function exportInventory(ExportService $exportService)
    {
        return $exportService->exportInventory();
    }
}
