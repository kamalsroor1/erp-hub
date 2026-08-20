<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class ReportController extends Controller
{
    /**
     * Display Profits & Business Analytics Executive Dashboard
     */
    public function index(Request $request)
    {
        $preset = $request->input('preset', 'this_month');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date', now()->toDateString());

        $filters = [
            'preset'    => $preset,
            'from_date' => $fromDate,
            'to_date'   => $toDate,
        ];

        $summaryData = ApiService::getReportSummary($filters);
        $topItemsData = ApiService::getReportTopItems($filters);
        $itemsData = ApiService::getItems();

        return Inertia::render('Reports/Index', [
            'period'    => $summaryData['period'] ?? ['preset' => $preset],
            'metrics'   => $summaryData['metrics'] ?? [],
            'top_items' => $topItemsData['top_items'] ?? [],
            'items'     => $itemsData['items'] ?? [],
            'filters'   => $filters,
        ]);
    }

    /**
     * Display Item Movement Card (كارت حركة الصنف)
     */
    public function itemCard(Request $request, $id)
    {
        $data = ApiService::getItemCard((int)$id);

        return Inertia::render('Reports/ItemCard', [
            'item'      => $data['item'] ?? null,
            'movements' => $data['movements'] ?? [],
        ]);
    }
}
