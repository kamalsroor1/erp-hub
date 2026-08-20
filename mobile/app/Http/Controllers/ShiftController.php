<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class ShiftController extends Controller
{
    /**
     * Display Shift Manager & Live Drawer Metrics
     */
    public function index(Request $request)
    {
        $data = ApiService::getCurrentShift();

        return Inertia::render('Shifts/Index', [
            'has_active'   => $data['has_active'] ?? false,
            'active_shift' => $data['active_shift'] ?? null,
            'metrics'      => $data['metrics'] ?? null,
        ]);
    }

    /**
     * Open a new shift
     */
    public function open(Request $request)
    {
        $validated = $request->validate([
            'opening_cash_balance' => 'required|numeric|min:0',
            'notes'                => 'nullable|string',
        ]);

        $res = ApiService::openShift($validated);

        if (!empty($res['success'])) {
            return redirect('/shifts')->with('success', $res['message'] ?? 'تم فتح الوردية بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل فتح الوردية');
    }

    /**
     * Close the active shift & generate Z-Report
     */
    public function close(Request $request)
    {
        $validated = $request->validate([
            'shift_id'            => 'required|integer',
            'actual_cash_balance' => 'required|numeric|min:0',
            'notes'               => 'nullable|string',
        ]);

        $res = ApiService::closeShift($validated);

        if (!empty($res['success'])) {
            $shiftId = $res['shift']['id'] ?? $validated['shift_id'];
            return redirect("/shifts/{$shiftId}/z-report")
                ->with('success', $res['message'] ?? 'تم تقفيل الوردية بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل تقفيل الوردية');
    }

    /**
     * Display & Print Thermal Z-Report
     */
    public function zReport(Request $request, $id)
    {
        $data = ApiService::getZReport((int)$id);

        return Inertia::render('Shifts/ZReport', [
            'report' => $data['report'] ?? null,
        ]);
    }
}
