<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display audit logs with filtering.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $query = AuditLog::where('company_id', $companyId)
            ->with('user')
            ->orderBy('created_at', 'desc');

        // Filter by model type
        if ($request->filled('model')) {
            $query->where('model_type', $request->model);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $logs = $query->paginate(50);

        // Get filter options
        $users = User::where('company_id', $companyId)->orderBy('name')->get();
        $modelTypes = [
            'App\\Models\\Journal' => 'Jurnal',
            'App\\Models\\ChartOfAccount' => 'Akun',
            'App\\Models\\Contact' => 'Kontak',
            'App\\Models\\Invoice' => 'Invoice',
            'App\\Models\\Inventory' => 'Persediaan',
            'App\\Models\\FixedAsset' => 'Aset Tetap',
        ];

        return view('audit-logs.index', compact('logs', 'users', 'modelTypes'));
    }

    /**
     * Show details of a specific audit log.
     */
    public function show(AuditLog $auditLog)
    {
        // Ensure same company
        if ($auditLog->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        return response()->json([
            'id' => $auditLog->id,
            'action' => $auditLog->action,
            'action_name' => $auditLog->action_name,
            'model_type' => $auditLog->model_type,
            'model_name' => $auditLog->model_name,
            'model_id' => $auditLog->model_id,
            'old_values' => $auditLog->old_values,
            'new_values' => $auditLog->new_values,
            'user' => $auditLog->user?->name ?? 'System',
            'ip_address' => $auditLog->ip_address,
            'created_at' => $auditLog->created_at->format('d M Y H:i:s'),
        ]);
    }
}
