<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuditLogController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function index()
    {
        return view('admin.audit-logs.index');
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');

        return view('admin.audit-logs.show', compact('auditLog'));
    }

    public function security()
    {
        $logs = $this->auditLogService->getSecurityLogs(30);

        return view('admin.audit-logs.security', compact('logs'));
    }

    public function export(Request $request)
    {
        try {
            $query = AuditLog::with('user')->orderByDesc('created_at');

            if ($request->has('from') && $request->from) {
                $query->whereDate('created_at', '>=', $request->from);
            }

            if ($request->has('to') && $request->to) {
                $query->whereDate('created_at', '<=', $request->to);
            }

            $logs = $query->get();

            $this->auditLogService->logExport('audit_logs', $logs->count());

            $filename = 'audit-logs-' . now()->format('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($logs) {
                $file = fopen('php://output', 'w');

                // Header row
                fputcsv($file, ['ID', 'User', 'Action', 'Description', 'IP Address', 'Date']);

                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->user?->full_name ?? 'System',
                        $log->action,
                        $log->description,
                        $log->ip_address,
                        $log->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('AuditLogController::export failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'Export failed. Please try again.');
        }
    }
}
