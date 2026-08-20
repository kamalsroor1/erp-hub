<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Services\ApiService;

class DashboardController extends Controller
{
    public function index()
    {
        $dash = ApiService::getDashboardSummary();

        return Inertia::render('Dashboard', [
            'user'             => ApiService::getUser(),
            'store'            => ApiService::getStore(),
            'customersCount'   => $dash['customers_count'] ?? 0,
            'suppliersCount'   => $dash['suppliers_count'] ?? 0,
            'totalReceivable'  => (string)($dash['total_receivable'] ?? '0.000'),
            'totalPayable'     => (string)($dash['total_payable'] ?? '0.000'),
            'todayMetrics'     => $dash['today_metrics'] ?? [],
            'currentShift'     => $dash['current_shift'] ?? null,
            'hasActiveShift'   => !empty($dash['has_active_shift']),
            'lowStockCount'    => $dash['low_stock_count'] ?? 0,
            'recentInvoices'   => $dash['recent_invoices'] ?? [],
            'recentLogs'       => $dash['recent_logs'] ?? [],
        ]);
    }
}
