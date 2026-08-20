<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->input('search', '');
        $category = $request->input('category', 'all');

        $res = ApiService::getItems($search, $category);

        return Inertia::render('Items/Index', [
            'items'      => $res['data'] ?? [],
            'categories' => $res['categories'] ?? [],
            'filters'    => [
                'search'   => $search,
                'category' => $category,
            ],
            'activeStore'=> ApiService::getStore(),
        ]);
    }
}
