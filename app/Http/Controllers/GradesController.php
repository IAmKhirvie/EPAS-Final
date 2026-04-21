<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use App\Models\User;
use App\Models\Module;
use App\Models\UserProgress;
use App\Models\HomeworkSubmission;
use App\Models\SelfCheckSubmission;
use App\Models\TaskSheetSubmission;
use App\Models\JobSheetSubmission;
use App\Models\PerformanceCriteria;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Handles grade viewing and management for the JOMS LMS.
 *
 * Provides different views based on user role:
 * - Students see their own grades
 * - Instructors see students in their advisory section
 * - Admins see all students system-wide
 */
class GradesController extends Controller
{
    protected GradingService $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Display grades view based on user role.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        if ($user->role === Roles::STUDENT) {
            return $this->studentGrades($user);
        }

        return $this->instructorAdminGrades($request, $user);
    }

    /**
     * Show detailed grades for a specific student.
     *
     * Students can only view their own grades.
     * Instructors can view students in their advisory section.
     * Admins can view any student.
     *
     * @param User $student
     * @return View
     */
    public function show(User $student): View
    {
        $viewer = Auth::user();

        // Students can only view their own grades
        if ($viewer->role === Roles::STUDENT && $viewer->id !== $student->id) {
            abort(403, 'You can only view your own grades.');
        }

        // Instructors can only view students in their assigned sections
        if ($viewer->role === Roles::INSTRUCTOR && !$viewer->isAssignedToSection($student->section)) {
            abort(403, 'You can only view grades for students in your assigned sections.');
        }

        return $this->studentGrades($student);
    }

    /**
     * API endpoint for student grades data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getStudentGradesApi(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if ($user->role !== Roles::STUDENT) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $summary = $this->calculateStudentGradeSummary($user);

            return response()->json([
                'summary' => $summary,
                'student' => [
                    'name' => $user->full_name,
                    'student_id' => $user->student_id,
                    'section' => $user->section,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('GradesController::getStudentGradesApi failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to load grades data.'], 500);
        }
    }

    /**
     * Export grades to CSV.
     *
     * @param Request $request
     * @return Response
     */
    public function exportGrades(Request $request): Response|RedirectResponse
    {
        try {
            $this->authorizeInstructor();

            $viewer = Auth::user();
            $section = $request->get('section');
            $courseId = $request->get('course_id');

            // Instructors can only export sections they are assigned to
            if ($viewer->role === Roles::INSTRUCTOR) {
                $assignedSections = $viewer->getAllAccessibleSections();
                if ($section && !$assignedSections->contains($section)) {
                    abort(403, 'You can only export grades for your assigned sections.');
                }
                // If no section specified, use the first assigned section
                if (!$section && $assignedSections->isNotEmpty()) {
                    $section = $assignedSections->first();
                }
            }

            $export = new \App\Exports\GradesExport($section, $courseId);
            $csv = $export->generateCSV();
            $filename = 'grades_' . ($section ?? 'all') . '_' . date('Y-m-d') . '.csv';

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('GradesController::exportGrades failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'Grade export failed. Please try again.');
        }
    }

    /**
     * Export class grades to CSV.
     *
     * @param string $section
     * @return Response
     */
    public function exportClassGrades(string $section): Response|RedirectResponse
    {
        try {
            $this->authorizeInstructor();

            $viewer = Auth::user();

            // Instructors can only export sections they are assigned to
            if ($viewer->role === Roles::INSTRUCTOR && !$viewer->isAssignedToSection($section)) {
                abort(403, 'You can only export grades for your assigned sections.');
            }

            $export = new \App\Exports\ClassGradesExport($section);
            $csv = $export->generateCSV();
            $filename = 'class_grades_' . $section . '_' . date('Y-m-d') . '.csv';

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('GradesController::exportClassGrades failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'Class grade export failed. Please try again.');
        }
    }

    // =========================================================================
    // PRIVATE METHODS - Grade Calculation
    // =========================================================================

    /**
     * Build student grades view with all module activities.
     *
     * @param User $student
     * @return View
     */
    private function studentGrades(User $student): View
    {
        $modules = Module::where('is_active', true)
            ->whereHas('course', fn($q) => $q->forSection($student->section))
            ->with([
                'informationSheets.selfChecks',
                'informationSheets.homeworks',
                'informationSheets.taskSheets',
                'informationSheets.jobSheets'
            ])
            ->get();

        // Pre-fetch all submissions for this student in batch (avoids N+1)
        $prefetched = $this->prefetchStudentSubmissions($student);

        $gradesData = [];
        $overallStats = $this->initializeOverallStats();

        foreach ($modules as $module) {
            $moduleGrades = $this->processModuleGrades($module, $student, $overallStats, $prefetched);
            $gradesData[] = $moduleGrades;
        }

        $this->finalizeOverallStats($overallStats);

        return view('grades.student', compact('gradesData', 'overallStats', 'student'));
    }

    /**
     * Pre-fetch all submission data for a student to avoid N+1 queries.
     */
    private function prefetchStudentSubmissions(User $student): array
    {
        $selfCheckSubs = SelfCheckSubmission::where('user_id', $student->id)
            ->orderByDesc('id')
            ->get()
            ->unique('self_check_id')
            ->keyBy('self_check_id');

        $homeworkSubs = HomeworkSubmission::where('user_id', $student->id)
            ->orderByDesc('id')
            ->get()
            ->unique('homework_id')
            ->keyBy('homework_id');

        $taskSheetSubs = TaskSheetSubmission::where('user_id', $student->id)
            ->orderByDesc('id')
            ->get()
            ->unique('task_sheet_id')
            ->keyBy('task_sheet_id');

        $jobSheetSubs = JobSheetSubmission::where('user_id', $student->id)
            ->orderByDesc('id')
            ->get()
            ->unique('job_sheet_id')
            ->keyBy('job_sheet_id');

        // Pre-fetch all performance criteria for this student's submissions
        $taskSubIds = $taskSheetSubs->pluck('id')->toArray();
        $jobSubIds = $jobSheetSubs->pluck('id')->toArray();

        $taskCriteria = !empty($taskSubIds)
            ? PerformanceCriteria::where('evaluable_type', TaskSheetSubmission::class)
                ->whereIn('evaluable_id', $taskSubIds)
                ->get()
                ->keyBy('evaluable_id')
            : collect();

        $jobCriteria = !empty($jobSubIds)
            ? PerformanceCriteria::where('evaluable_type', JobSheetSubmission::class)
                ->whereIn('evaluable_id', $jobSubIds)
                ->get()
                ->keyBy('evaluable_id')
            : collect();

        return compact('selfCheckSubs', 'homeworkSubs', 'taskSheetSubs', 'jobSheetSubs', 'taskCriteria', 'jobCriteria');
    }

    /**
     * Build instructor/admin grades view with student list.
     *
     * @param Request $request
     * @param User $viewer
     * @return View
     */
    private function instructorAdminGrades(Request $request, User $viewer): View
    {
        return view('grades.instructor', ['viewer' => $viewer]);
    }

    // =========================================================================
    // PRIVATE METHODS - Helper Functions
    // =========================================================================

    /**
     * Initialize the overall statistics array.
     *
     * @return array
     */
    private function initializeOverallStats(): array
    {
        return [
            'total_activities' => 0,
            'completed' => 0,
            'total_score' => 0,
            'max_score' => 0,
        ];
    }

    /**
     * Process all grades for a single module.
     *
     * @param Module $module
     * @param User $student
     * @param array &$overallStats Reference to overall statistics
     * @return array Module grades data
     */
    private function processModuleGrades(Module $module, User $student, array &$overallStats, array $prefetched = []): array
    {
        $moduleGrades = [
            'module' => $module,
            'self_checks' => [],
            'homeworks' => [],
            'task_sheets' => [],
            'job_sheets' => [],
            'module_average' => 0,
            'completion_rate' => 0,
        ];

        $moduleScore = 0;
        $moduleMaxScore = 0;
        $moduleCompleted = 0;
        $moduleTotal = 0;

        foreach ($module->informationSheets as $sheet) {
            // Process each activity type
            $this->processSelfChecks($sheet, $student, $moduleGrades, $overallStats, $moduleScore, $moduleMaxScore, $moduleCompleted, $moduleTotal, $prefetched);
            $this->processHomeworks($sheet, $student, $moduleGrades, $overallStats, $moduleScore, $moduleMaxScore, $moduleCompleted, $moduleTotal, $prefetched);
            $this->processTaskSheets($sheet, $student, $moduleGrades, $overallStats, $moduleScore, $moduleMaxScore, $moduleCompleted, $moduleTotal, $prefetched);
            $this->processJobSheets($sheet, $student, $moduleGrades, $overallStats, $moduleScore, $moduleMaxScore, $moduleCompleted, $moduleTotal, $prefetched);
        }

        // Calculate module statistics
        $moduleGrades['module_average'] = $moduleMaxScore > 0
            ? round(($moduleScore / $moduleMaxScore) * 100, 1)
            : 0;
        $moduleGrades['completion_rate'] = $moduleTotal > 0
            ? round(($moduleCompleted / $moduleTotal) * 100, 1)
            : 0;
        $moduleGrades['completed_count'] = $moduleCompleted;
        $moduleGrades['total_count'] = $moduleTotal;

        return $moduleGrades;
    }

    /**
     * Process self-check submissions for a student.
     *
     * @param mixed $sheet Information sheet
     * @param User $student
     * @param array &$moduleGrades
     * @param array &$overallStats
     * @param float &$moduleScore
     * @param float &$moduleMaxScore
     * @param int &$moduleCompleted
     * @param int &$moduleTotal
     */
    private function processSelfChecks($sheet, User $student, array &$moduleGrades, array &$overallStats, float &$moduleScore, float &$moduleMaxScore, int &$moduleCompleted, int &$moduleTotal, array $prefetched = []): void
    {
        foreach ($sheet->selfChecks as $selfCheck) {
            $moduleTotal++;
            $overallStats['total_activities']++;

            $submission = !empty($prefetched)
                ? ($prefetched['selfCheckSubs']->get($selfCheck->id))
                : SelfCheckSubmission::where('user_id', $student->id)
                    ->where('self_check_id', $selfCheck->id)
                    ->latest()
                    ->first();

            $moduleGrades['self_checks'][] = [
                'id' => $selfCheck->id,
                'title' => $selfCheck->title,
                'information_sheet' => $sheet->title,
                'submission' => $submission,
                'score' => $submission?->score,
                'max_score' => $submission?->total_points ?? $selfCheck->total_points,
                'percentage' => $submission?->percentage,
                'grade' => $submission?->grade,
                'passed' => $submission?->passed ?? false,
                'completed_at' => $submission?->created_at,
            ];

            if ($submission) {
                $moduleCompleted++;
                $overallStats['completed']++;
                $moduleScore += $submission->score ?? 0;
                $moduleMaxScore += $submission->total_points ?? 0;
                $overallStats['total_score'] += $submission->score ?? 0;
                $overallStats['max_score'] += $submission->total_points ?? 0;
            }
        }
    }

    /**
     * Process homework submissions for a student.
     *
     * @param mixed $sheet Information sheet
     * @param User $student
     * @param array &$moduleGrades
     * @param array &$overallStats
     * @param float &$moduleScore
     * @param float &$moduleMaxScore
     * @param int &$moduleCompleted
     * @param int &$moduleTotal
     */
    private function processHomeworks($sheet, User $student, array &$moduleGrades, array &$overallStats, float &$moduleScore, float &$moduleMaxScore, int &$moduleCompleted, int &$moduleTotal, array $prefetched = []): void
    {
        foreach ($sheet->homeworks as $homework) {
            $moduleTotal++;
            $overallStats['total_activities']++;

            $submission = !empty($prefetched)
                ? ($prefetched['homeworkSubs']->get($homework->id))
                : HomeworkSubmission::where('user_id', $student->id)
                    ->where('homework_id', $homework->id)
                    ->latest()
                    ->first();

            $percentage = null;
            if ($submission && $homework->max_points > 0) {
                $percentage = round(($submission->score / $homework->max_points) * 100, 1);
            }

            $moduleGrades['homeworks'][] = [
                'id' => $homework->id,
                'title' => $homework->title,
                'information_sheet' => $sheet->title,
                'submission' => $submission,
                'score' => $submission?->score,
                'max_score' => $homework->max_points,
                'percentage' => $percentage,
                'grade' => $submission?->grade,
                'evaluated' => $submission?->evaluated_at !== null,
                'is_late' => $submission?->is_late ?? false,
                'submitted_at' => $submission?->created_at,
                'evaluator_notes' => $submission?->evaluator_notes,
            ];

            if ($submission && $submission->evaluated_at) {
                $moduleCompleted++;
                $overallStats['completed']++;
                $moduleScore += $submission->score ?? 0;
                $moduleMaxScore += $homework->max_points;
                $overallStats['total_score'] += $submission->score ?? 0;
                $overallStats['max_score'] += $homework->max_points;
            }
        }
    }

    /**
     * Process task sheet submissions for a student.
     *
     * @param mixed $sheet Information sheet
     * @param User $student
     * @param array &$moduleGrades
     * @param array &$overallStats
     * @param float &$moduleScore
     * @param float &$moduleMaxScore
     * @param int &$moduleCompleted
     * @param int &$moduleTotal
     */
    private function processTaskSheets($sheet, User $student, array &$moduleGrades, array &$overallStats, float &$moduleScore, float &$moduleMaxScore, int &$moduleCompleted, int &$moduleTotal, array $prefetched = []): void
    {
        foreach ($sheet->taskSheets as $taskSheet) {
            $moduleTotal++;
            $overallStats['total_activities']++;

            $submission = !empty($prefetched)
                ? ($prefetched['taskSheetSubs']->get($taskSheet->id))
                : TaskSheetSubmission::where('user_id', $student->id)
                    ->where('task_sheet_id', $taskSheet->id)
                    ->latest()
                    ->first();

            $criteria = null;
            if ($submission) {
                $criteria = !empty($prefetched)
                    ? ($prefetched['taskCriteria']->get($submission->id))
                    : PerformanceCriteria::where('evaluable_type', TaskSheetSubmission::class)
                        ->where('evaluable_id', $submission->id)
                        ->first();
            }

            $moduleGrades['task_sheets'][] = [
                'id' => $taskSheet->id,
                'title' => $taskSheet->title,
                'information_sheet' => $sheet->title,
                'submission' => $submission,
                'criteria' => $criteria,
                'score' => $criteria?->score,
                'grade' => $criteria?->grade,
                'submitted_at' => $submission?->submitted_at,
                'evaluator_notes' => $criteria?->evaluator_notes,
            ];

            if ($submission && $criteria) {
                $moduleCompleted++;
                $overallStats['completed']++;
                $moduleScore += $criteria->score ?? 0;
                $moduleMaxScore += 100; // Task sheets are scored out of 100
                $overallStats['total_score'] += $criteria->score ?? 0;
                $overallStats['max_score'] += 100;
            }
        }
    }

    /**
     * Process job sheet submissions for a student.
     *
     * @param mixed $sheet Information sheet
     * @param User $student
     * @param array &$moduleGrades
     * @param array &$overallStats
     * @param float &$moduleScore
     * @param float &$moduleMaxScore
     * @param int &$moduleCompleted
     * @param int &$moduleTotal
     */
    private function processJobSheets($sheet, User $student, array &$moduleGrades, array &$overallStats, float &$moduleScore, float &$moduleMaxScore, int &$moduleCompleted, int &$moduleTotal, array $prefetched = []): void
    {
        foreach ($sheet->jobSheets as $jobSheet) {
            $moduleTotal++;
            $overallStats['total_activities']++;

            $submission = !empty($prefetched)
                ? ($prefetched['jobSheetSubs']->get($jobSheet->id))
                : JobSheetSubmission::where('user_id', $student->id)
                    ->where('job_sheet_id', $jobSheet->id)
                    ->latest()
                    ->first();

            $criteria = null;
            if ($submission) {
                $criteria = !empty($prefetched)
                    ? ($prefetched['jobCriteria']->get($submission->id))
                    : PerformanceCriteria::where('evaluable_type', JobSheetSubmission::class)
                        ->where('evaluable_id', $submission->id)
                        ->first();
            }

            $moduleGrades['job_sheets'][] = [
                'id' => $jobSheet->id,
                'title' => $jobSheet->title,
                'information_sheet' => $sheet->title,
                'submission' => $submission,
                'criteria' => $criteria,
                'score' => $criteria?->score,
                'grade' => $criteria?->grade,
                'completion_percentage' => $submission?->completion_percentage,
                'submitted_at' => $submission?->created_at,
                'evaluator_notes' => $criteria?->evaluator_notes ?? $submission?->evaluator_notes,
            ];

            if ($submission && $criteria) {
                $moduleCompleted++;
                $overallStats['completed']++;
                $moduleScore += $criteria->score ?? 0;
                $moduleMaxScore += 100; // Job sheets are scored out of 100
                $overallStats['total_score'] += $criteria->score ?? 0;
                $overallStats['max_score'] += 100;
            }
        }
    }

    /**
     * Finalize overall statistics with averages and grades.
     *
     * @param array &$overallStats
     */
    private function finalizeOverallStats(array &$overallStats): void
    {
        $overallStats['average'] = $overallStats['max_score'] > 0
            ? round(($overallStats['total_score'] / $overallStats['max_score']) * 100, 1)
            : 0;

        $overallStats['completion_rate'] = $overallStats['total_activities'] > 0
            ? round(($overallStats['completed'] / $overallStats['total_activities']) * 100, 1)
            : 0;

        $grade = $this->gradingService->applyGradingScale($overallStats['average']);
        $overallStats['grade'] = $grade;
        $overallStats['grade_descriptor'] = $grade['descriptor'];
        $overallStats['grade_code'] = $grade['code'];
        $overallStats['is_competent'] = $grade['is_competent'];
    }

    /**
     * Calculate a quick grade summary for a student (used in list views).
     *
     * When called from batch context, pre-fetched values are passed to avoid N+1 queries.
     *
     * @param User $student
     * @param float|null $prefetchedSelfCheckAvg
     * @param float|null $prefetchedHomeworkAvg
     * @param int|null $prefetchedCompletedCount
     * @return array
     */
    /**
     * Export the authenticated student's own grades to CSV.
     */
    public function exportMyGrades(): Response
    {
        $user = Auth::user();

        if ($user->role !== Roles::STUDENT) {
            abort(403);
        }

        $summary = $this->calculateStudentGradeSummary($user);

        // Build CSV
        $lines = [];
        $lines[] = implode(',', ['Student Name', 'Student ID', 'Section', 'Self-Check Avg', 'Homework Avg', 'Overall Avg', 'Grade', 'Status']);
        $lines[] = implode(',', [
            '"' . $user->full_name . '"',
            $user->student_id ?? 'N/A',
            $user->section ?? 'N/A',
            $summary['self_check_average'],
            $summary['homework_average'],
            $summary['overall_average'],
            $summary['grade_descriptor'],
            $summary['competency_status'],
        ]);

        $csv = implode("\n", $lines);
        $filename = 'my_grades_' . date('Y-m-d') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function calculateStudentGradeSummary(
        User $student,
        ?float $prefetchedSelfCheckAvg = null,
        ?float $prefetchedHomeworkAvg = null,
        ?int $prefetchedCompletedCount = null
    ): array {
        // Use pre-fetched data if available, otherwise query per-student
        $selfCheckAvg = $prefetchedSelfCheckAvg ?? (SelfCheckSubmission::where('user_id', $student->id)
            ->whereNotNull('percentage')
            ->max('percentage') ?? 0);

        $homeworkAvg = $prefetchedHomeworkAvg ?? (HomeworkSubmission::where('user_id', $student->id)
            ->whereNotNull('score')
            ->selectRaw('AVG(score / (SELECT max_points FROM homeworks WHERE homeworks.id = homework_submissions.homework_id) * 100) as avg')
            ->value('avg') ?? 0);

        $completedActivities = $prefetchedCompletedCount ?? UserProgress::where('user_id', $student->id)
            ->where('status', 'completed')
            ->count();

        // Calculate overall average
        $overallAvg = ($selfCheckAvg + $homeworkAvg) / 2;
        $grade = $this->gradingService->applyGradingScale($overallAvg);

        return [
            'overall_average' => round($overallAvg, 1),
            'grade' => $grade,
            'grade_descriptor' => $grade['descriptor'],
            'grade_code' => $grade['code'],
            'is_competent' => $grade['is_competent'],
            'competency_status' => $grade['competency_status'],
            'self_check_average' => round($selfCheckAvg, 1),
            'homework_average' => round($homeworkAvg, 1),
            'completed_activities' => $completedActivities,
            'total_points' => $student->total_points ?? 0,
        ];
    }
}
