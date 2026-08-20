<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class TreasuryController extends Controller
{
    public function index()
    {
        $res = ApiService::getTreasurySummary();

        return Inertia::render('Treasury/Index', [
            'treasury'    => $res['today'] ?? [],
            'balances'    => $res['balances'] ?? [],
            'activeShift' => $res['active_shift'] ?? null,
            'activeStore' => ApiService::getStore(),
        ]);
    }
}
