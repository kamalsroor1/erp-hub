<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Actions\Dashboard\GetTenantDashboardAnalyticsAction;

class DashboardController extends Controller
{
    public function __construct(
        protected GetTenantDashboardAnalyticsAction $getDashboardAnalyticsAction
    ) {}

    /**
     * Display the rich Inertia Vue 3 Dashboard
     */
    public function index(Request $request): Response
    {
        $dashboardData = $this->getDashboardAnalyticsAction->execute($request->user());

        return Inertia::render('Dashboard', $dashboardData);
    }
}
