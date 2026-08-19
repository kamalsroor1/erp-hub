<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\Store;

class ActivityLogController extends Controller
{
    /**
     * List system activity & audit logs
     */
    public function index(Request $request)
    {
        $module = $request->input('module'); // 'all', 'sales', 'inventory', 'purchases', 'shifts', 'expenses'
        $search = $request->input('search');
        $userId = $request->input('user_id');

        $query = ActivityLog::with(['user', 'store'])
            ->when($module && $module !== 'all', fn($q) => $q->where('module', $module))
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
                });
            });

        $totalCount = (clone $query)->count();
        $logs = $query->latest('id')->paginate(35);

        $formattedLogs = collect($logs->items())->map(function (ActivityLog $log) {
            $badge = $log->module_badge;
            return [
                'id'           => $log->id,
                'module'       => $log->module,
                'module_label' => $badge['label'] ?? $log->module,
                'module_color' => $badge['color'] ?? 'slate',
                'module_icon'  => $badge['icon'] ?? '⚙️',
                'action'       => $log->action,
                'description'  => $log->description,
                'properties'   => $log->properties,
                'user_name'    => $log->user?->name ?? 'النظام التلقائي',
                'store_name'   => $log->store?->name ?? 'الفرع الرئيسي',
                'created_at'   => $log->created_at?->format('Y-m-d H:i:s'),
                'time_ago'     => $log->created_at?->diffForHumans(),
            ];
        });

        return response()->json([
            'success'     => true,
            'logs'        => $formattedLogs,
            'total_count' => $totalCount,
            'pagination'  => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
            ]
        ]);
    }
}
