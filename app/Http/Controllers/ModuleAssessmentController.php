<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleAssessmentSubmission;
use App\Services\ModuleAssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleAssessmentController extends Controller
{
    protected ModuleAssessmentService $assessmentService;

    public function __construct(ModuleAssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    /**
     * Show the assessment page.
     */
    public function show(Course $course, Module $module)
    {
        $user = Auth::user();

        // Check if user can take assessment
        $eligibility = $this->assessmentService->canUserTakeAssessment($module, $user);

        if (!$eligibility['can_take']) {
            // If they have already passed, show results
            if (isset($eligibility['details']['best_attempt'])) {
                return redirect()->route('courses.modules.assessment.results', [
                    $course,
                    $module,
                    $eligibility['details']['best_attempt']->id,
                ]);
            }

            return view('modules.assessment.blocked', [
                'course' => $course,
                'module' => $module,
                'reason' => $eligibility['reason'],
                'details' => $eligibility['details'] ?? [],
            ]);
        }

        // Get or start assessment
        $submission = $eligibility['in_progress'] ?? null;
        if (!$submission) {
            $submission = $this->assessmentService->startAssessment($module, $user);
        }

        // Get questions for this assessment
        $questions = $this->assessmentService->getAssessmentQuestions($module, $submission);

        // Load existing answers if resuming
        $savedAnswers = $submission->answers ?? [];

        return view('modules.assessment.show', [
            'course' => $course,
            'module' => $module,
            'submission' => $submission,
            'questions' => $questions,
            'savedAnswers' => $savedAnswers,
            'timeLimit' => $module->assessment_time_limit,
            'remainingTime' => $submission->remaining_time,
        ]);
    }

    /**
     * Submit the assessment.
     */
    public function submit(Request $request, Course $course, Module $module)
    {
        $user = Auth::user();

        // Get the in-progress submission
        $submission = $module->getInProgressAssessmentFor($user);

        if (!$submission) {
            return redirect()->route('courses.modules.show', [$course, $module])
                ->with('error', 'No active assessment found.');
        }

        // Check if timed out
        if ($submission->hasExpired()) {
            $submission->markAsTimedOut();
            return redirect()->route('courses.modules.assessment.results', [$course, $module, $submission->id])
                ->with('warning', 'Your assessment has timed out.');
        }

        // Get answers from request
        $answers = $request->input('answers', []);

        // Grade and complete the assessment
        $submission = $this->assessmentService->submitAssessment($submission, $answers);

        return redirect()->route('courses.modules.assessment.results', [$course, $module, $submission->id])
            ->with('success', 'Assessment submitted successfully!');
    }

    /**
     * Save progress without submitting.
     */
    public function saveProgress(Request $request, Course $course, Module $module)
    {
        $user = Auth::user();
        $submission = $module->getInProgressAssessmentFor($user);

        if (!$submission) {
            return response()->json(['error' => 'No active assessment found'], 404);
        }

        $answers = $request->input('answers', []);
        $submission->update(['answers' => $answers]);

        return response()->json([
            'success' => true,
            'remaining_time' => $submission->remaining_time,
        ]);
    }

    /**
     * Show assessment results.
     */
    public function results(Course $course, Module $module, ModuleAssessmentSubmission $submission)
    {
        $user = Auth::user();

        // Verify submission belongs to user or user is admin/instructor
        if ($submission->user_id !== $user->id && !in_array($user->role, ['admin', 'instructor'])) {
            abort(403, 'Unauthorized');
        }

        // Get questions with user answers
        $questions = $this->assessmentService->getAssessmentQuestions($module, $submission);
        $gradingDetails = collect($submission->grading_details ?? [])->keyBy('question_id');

        // Determine if we can show correct answers
        $showAnswers = $module->assessment_show_answers;

        // Get user's assessment history
        $history = $this->assessmentService->getUserAssessmentHistory($module, $user);

        // Check if user can retake
        $canRetake = $module->canTakeAssessment($user);

        return view('modules.assessment.results', [
            'course' => $course,
            'module' => $module,
            'submission' => $submission,
            'questions' => $questions,
            'gradingDetails' => $gradingDetails,
            'showAnswers' => $showAnswers,
            'history' => $history,
            'canRetake' => $canRetake,
        ]);
    }

    /**
     * Show assessment history for a user.
     */
    public function history(Course $course, Module $module)
    {
        $user = Auth::user();
        $history = $this->assessmentService->getUserAssessmentHistory($module, $user);

        return view('modules.assessment.history', [
            'course' => $course,
            'module' => $module,
            'history' => $history,
        ]);
    }

    /**
     * Admin: Show assessment statistics.
     */
    public function stats(Course $course, Module $module)
    {
        $this->authorize('manage', $course);

        $stats = $this->assessmentService->getAssessmentStats($module);
        $submissions = $module->assessmentSubmissions()
            ->with('user')
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('modules.assessment.stats', [
            'course' => $course,
            'module' => $module,
            'stats' => $stats,
            'submissions' => $submissions,
        ]);
    }
}
