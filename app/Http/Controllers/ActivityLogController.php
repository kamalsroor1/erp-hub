<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string)$request->input('search', ''));
        $module = $request->input('module', 'all');
        $action = $request->input('action', 'all');
        $dateFrom = $request->input('from');
        $dateTo = $request->input('to');

        $query = ActivityLog::with(['user', 'store']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($module !== 'all') {
            $query->where('module', $module);
        }

        if ($action !== 'all') {
            $query->where('action', $action);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->latest('id')->paginate(20)->withQueryString();

        $modulesList = [
            'sales'      => 'المبيعات و POS 🛒',
            'inventory'  => 'الأصناف والمخزون 📦',
            'shifts'     => 'الخزينة والورديات 💵',
            'purchases'  => 'المشتريات والتوريد 🚚',
            'expenses'   => 'المصروفات 💸',
            'contacts'   => 'العملاء والموردين 👥',
            'auth'       => 'الأمان والدخول 🔐',
            'system'     => 'إدارة النظام ⚙️',
        ];

        return Inertia::render('ActivityLogs/Index', [
            'logs' => $logs->through(fn($l) => [
                'id' => $l->id,
                'module' => $l->module,
                'module_badge' => $l->module_badge,
                'action' => $l->action,
                'description' => $l->description,
                'ip_address' => $l->ip_address,
                'user_name' => $l->user?->name ?: 'النظام التلقائي',
                'store_name' => $l->store?->name,
                'created_at' => $l->created_at->format('Y-m-d H:i:s'),
                'time_ago' => $l->created_at->diffForHumans(),
            ]),
            'modules' => $modulesList,
            'filters' => [
                'search' => $search,
                'module' => $module,
                'action' => $action,
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
        ]);
    }
}