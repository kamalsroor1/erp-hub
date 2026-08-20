<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class ActivityLogController extends Controller
{
    /**
     * Display live Activity & Audit Trail timeline
     */
    public function index(Request $request)
    {
        $filters = $request->only(['module', 'search', 'page']);
        $data = ApiService::getActivityLogs($filters);

        return Inertia::render('AuditLogs/Index', [
            'logs'        => $data['logs'] ?? [],
            'total_count' => $data['total_count'] ?? 0,
            'pagination'  => $data['pagination'] ?? [],
            'filters'     => $filters,
        ]);
    }
}
