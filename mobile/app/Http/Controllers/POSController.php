<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class POSController extends Controller
{
    public function index(Request $request)
    {
        $itemsRes     = ApiService::getItems();
        $customersRes = ApiService::getCustomers('', 'all', 1);

        return Inertia::render('POS/Index', [
            'items'       => $itemsRes['data'] ?? [],
            'categories'  => $itemsRes['categories'] ?? [],
            'customers'   => $customersRes['data'] ?? [],
            'activeStore' => ApiService::getStore(),
        ]);
    }
}
