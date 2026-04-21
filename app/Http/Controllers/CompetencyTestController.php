<?php

namespace App\Http\Controllers;

use App\Models\CompetencyTest;
use App\Models\CompetencyTestSubmission;
use App\Models\Course;
use App\Models\Module;
use App\Services\CompetencyTestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompetencyTestController extends Controller
{
    protected CompetencyTestService $testService;

    public function __construct(CompetencyTestService $testService)
    {
        $this->testService = $testService;
    }

    /**
     * Show list of competency tests for a module.
     */
    public function index(Course $course, Module $module)
    {
        $tests = $module->competencyTests()->orderBy('order')->get();

        return view('modules.competency-tests.index', [
            'course' => $course,
            'module' => $module,
            'tests' => $tests,
        ]);
    }

    /**
     * Show create form.
     */
    public function create(Course $course, Module $module)
    {
        return view('modules.competency-tests.create', [
            'course' => $course,
            'module' => $module,
        ]);
    }

    /**
     * Store a new competency test.
     */
    public function store(Request $request, Course $course, Module $module)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'max_attempts' => 'nullable|integer|min:1',
            'reveal_answers' => 'boolean',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'parts' => 'nullable|array',
            'parts.*.name' => 'required|string|max:255',
            'parts.*.instructions' => 'nullable|string',
        ]);

        $validated['reveal_answers'] = $request->boolean('reveal_answers');
        $validated['randomize_questions'] = $request->boolean('randomize_questions');
        $validated['randomize_options'] = $request->boolean('randomize_options');

        $test = $this->testService->createTest($module, $validated);

        return redirect()->route('courses.modules.competency-tests.edit', [$course, $module, $test])
            ->with('success', 'Competency Test created! Now add questions.');
    }

    /**
     * Show edit form.
     */
    public function edit(Course $course, Module $module, CompetencyTest $competencyTest)
    {
        $competencyTest->load('questions');

        return view('modules.competency-tests.edit', [
            'course' => $course,
            'module' => $module,
            'test' => $competencyTest,
        ]);
    }

    /**
     * Update a competency test.
     */
    public function update(Request $request, Course $course, Module $module, CompetencyTest $competencyTest)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'max_attempts' => 'nullable|integer|min:1',
            'reveal_answers' => 'boolean',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'is_active' => 'boolean',
            'parts' => 'nullable|array',
        ]);

        $validated['reveal_answers'] = $request->boolean('reveal_answers');
        $validated['randomize_questions'] = $request->boolean('randomize_questions');
        $validated['randomize_options'] = $request->boolean('randomize_options');
        $validated['is_active'] = $request->boolean('is_active');

        $competencyTest->update($validated);

        return back()->with('success', 'Competency Test updated successfully.');
    }

    /**
     * Delete a competency test.
     */
    public function destroy(Course $course, Module $module, CompetencyTest $competencyTest)
    {
        $competencyTest->delete();

        return redirect()->route('courses.modules.show', [$course, $module])
            ->with('success', 'Competency Test deleted.');
    }

    /**
     * Show the test (for taking).
     */
    public function show(Course $course, Module $module, CompetencyTest $competencyTest)
    {
        $user = Auth::user();

        // Check eligibility
        $eligibility = $this->testService->canUserTakeTest($competencyTest, $user);

        if (!$eligibility['can_take']) {
            if (isset($eligibility['details']['best_attempt'])) {
                return redirect()->route('courses.modules.competency-tests.results', [
                    $course, $module, $competencyTest, $eligibility['details']['best_attempt']->id,
                ]);
            }

            return view('modules.competency-tests.blocked', [
                'course' => $course,
                'module' => $module,
                'test' => $competencyTest,
                'reason' => $eligibility['reason'],
                'details' => $eligibility['details'] ?? [],
            ]);
        }

        // Get or start submission
        $submission = $eligibility['in_progress'] ?? null;
        if (!$submission) {
            $submission = $this->testService->startTest($competencyTest, $user);
        }

        // Get questions (with randomization if enabled)
        $questions = $competencyTest->getRandomizedQuestions($user->id);

        // Group by parts
        $parts = $competencyTest->parts ?? [];
        $questionsByPart = [];

        if (!empty($parts)) {
            foreach ($parts as $index => $part) {
                $questionsByPart[$index] = [
                    'part' => $part,
                    'questions' => $questions->where('part_index', $index)->values(),
                ];
            }
            // Unassigned questions
            $unassigned = $questions->whereNull('part_index')->values();
            if ($unassigned->isNotEmpty()) {
                $questionsByPart['unassigned'] = [
                    'part' => ['name' => 'Additional Questions', 'instructions' => null],
                    'questions' => $unassigned,
                ];
            }
        } else {
            $questionsByPart['all'] = [
                'part' => ['name' => null, 'instructions' => null],
                'questions' => $questions,
            ];
        }

        return view('modules.competency-tests.show', [
            'course' => $course,
            'module' => $module,
            'test' => $competencyTest,
            'submission' => $submission,
            'questionsByPart' => $questionsByPart,
            'savedAnswers' => $submission->answers ?? [],
            'timeLimit' => $competencyTest->time_limit,
            'remainingTime' => $submission->remaining_time,
        ]);
    }

    /**
     * Submit the test.
     */
    public function submit(Request $request, Course $course, Module $module, CompetencyTest $competencyTest)
    {
        $user = Auth::user();

        $submission = $this->testService->getInProgressSubmission($competencyTest, $user);

        if (!$submission) {
            return redirect()->route('courses.modules.competency-tests.show', [$course, $module, $competencyTest])
                ->with('error', 'No active test found.');
        }

        if ($submission->hasExpired()) {
            $submission->markAsTimedOut();
            return redirect()->route('courses.modules.competency-tests.results', [$course, $module, $competencyTest, $submission->id])
                ->with('warning', 'Your test has timed out.');
        }

        $answers = $request->input('answers', []);
        $submission = $this->testService->submitTest($submission, $answers);

        return redirect()->route('courses.modules.competency-tests.results', [$course, $module, $competencyTest, $submission->id])
            ->with('success', 'Test submitted successfully!');
    }

    /**
     * Save progress without submitting.
     */
    public function saveProgress(Request $request, Course $course, Module $module, CompetencyTest $competencyTest)
    {
        $user = Auth::user();
        $submission = $this->testService->getInProgressSubmission($competencyTest, $user);

        if (!$submission) {
            return response()->json(['error' => 'No active test found'], 404);
        }

        $answers = $request->input('answers', []);
        $this->testService->saveProgress($submission, $answers);

        return response()->json([
            'success' => true,
            'remaining_time' => $submission->remaining_time,
        ]);
    }

    /**
     * Show results.
     */
    public function results(Course $course, Module $module, CompetencyTest $competencyTest, CompetencyTestSubmission $submission)
    {
        $user = Auth::user();

        // Verify access
        if ($submission->user_id !== $user->id && !in_array($user->role, ['admin', 'instructor'])) {
            abort(403);
        }

        $questions = $competencyTest->questions;
        $gradingDetails = collect($submission->grading_details ?? [])->keyBy('question_id');

        // Group by parts
        $parts = $competencyTest->parts ?? [];
        $questionsByPart = [];

        if (!empty($parts)) {
            foreach ($parts as $index => $part) {
                $partQuestions = $questions->where('part_index', $index)->values();
                $questionsByPart[$index] = [
                    'part' => $part,
                    'questions' => $partQuestions,
                    'score' => $gradingDetails->whereIn('question_id', $partQuestions->pluck('id'))->sum('points_earned'),
                    'total' => $partQuestions->sum('points'),
                ];
            }
        }

        $history = $this->testService->getUserTestHistory($competencyTest, $user);
        $canRetake = $competencyTest->canBeTakenBy($user);

        return view('modules.competency-tests.results', [
            'course' => $course,
            'module' => $module,
            'test' => $competencyTest,
            'submission' => $submission,
            'questions' => $questions,
            'questionsByPart' => $questionsByPart,
            'gradingDetails' => $gradingDetails,
            'showAnswers' => $competencyTest->reveal_answers,
            'history' => $history,
            'canRetake' => $canRetake,
        ]);
    }

    /**
     * Store questions (AJAX).
     */
    public function storeQuestions(Request $request, Course $course, Module $module, CompetencyTest $competencyTest)
    {
        $validated = $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.question_type' => 'required|string',
            'questions.*.points' => 'nullable|integer|min:1',
            'questions.*.options' => 'nullable|array',
            'questions.*.correct_answer' => 'nullable',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.part_index' => 'nullable|integer',
        ]);

        $this->testService->addQuestions($competencyTest, $validated['questions']);

        return response()->json(['success' => true, 'message' => 'Questions added successfully.']);
    }

    /**
     * Admin: Show test statistics.
     */
    public function stats(Course $course, Module $module, CompetencyTest $competencyTest)
    {
        $stats = $this->testService->getTestStats($competencyTest);
        $submissions = $competencyTest->submissions()
            ->with('user')
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('modules.competency-tests.stats', [
            'course' => $course,
            'module' => $module,
            'test' => $competencyTest,
            'stats' => $stats,
            'submissions' => $submissions,
        ]);
    }
}
