<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentProgressExport;
use Barryvdh\DomPDF\Facade\Pdf;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;
    protected AuditLogService $auditLogService;

    public function __construct(AnalyticsService $analyticsService, AuditLogService $auditLogService)
    {
        $this->analyticsService = $analyticsService;
        $this->auditLogService = $auditLogService;
    }

    public function dashboard()
    {
        $metrics = $this->analyticsService->getDashboardMetrics();

        return view('analytics.dashboard', compact('metrics'));
    }

    public function users()
    {
        $metrics = $this->analyticsService->getUserMetrics();

        return view('analytics.users', compact('metrics'));
    }

    public function courses()
    {
        $metrics = $this->analyticsService->getCourseMetrics();

        return view('analytics.courses', compact('metrics'));
    }

    public function getMetricsApi()
    {
        $metrics = $this->analyticsService->getDashboardMetrics();

        return response()->json($metrics);
    }

    public function exportStudentProgress(Request $request)
    {
        try {
            $this->auditLogService->logExport('student_progress', 0);

            return Excel::download(
                new StudentProgressExport($request->all()),
                'student-progress-' . now()->format('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            Log::error('AnalyticsController::exportStudentProgress failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'Export failed. Please try again.');
        }
    }

    public function exportPdfReport()
    {
        try {
            $metrics = $this->analyticsService->getDashboardMetrics();

            $pdf = Pdf::loadView('analytics.pdf-report', compact('metrics'))
                ->setPaper('a4', 'portrait');

            $this->auditLogService->logExport('analytics_report', 1);

            return $pdf->download('analytics-report-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('AnalyticsController::exportPdfReport failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'PDF export failed. Please try again.');
        }
    }

    public function refreshCache()
    {
        try {
            $this->analyticsService->clearCache();

            return response()->json(['message' => 'Analytics cache refreshed']);
        } catch (\Exception $e) {
            Log::error('AnalyticsController::refreshCache failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return response()->json(['error' => 'Cache refresh failed. Please try again.'], 500);
        }
    }

    public function topPerformers(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $performers = $this->analyticsService->getTopPerformers($limit);

            return response()->json($performers);
        } catch (\Exception $e) {
            Log::error('AnalyticsController::topPerformers failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to load top performers.'], 500);
        }
    }

    public function atRiskStudents()
    {
        try {
            $students = $this->analyticsService->getAtRiskStudents();

            return response()->json($students);
        } catch (\Exception $e) {
            Log::error('AnalyticsController::atRiskStudents failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to load at-risk students.'], 500);
        }
    }
}
