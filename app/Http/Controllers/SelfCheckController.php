<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\InformationSheet;
use App\Models\SelfCheck;
use App\Models\SelfCheckQuestion;
use App\Services\NotificationService;
use App\Services\ProgressTrackingService;
use App\Services\SelfCheckGradingService;
use App\Services\DocumentConversionService;
use App\Http\Requests\StoreSelfCheckRequest;
use App\Http\Requests\UpdateSelfCheckRequest;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * SelfCheckController
 *
 * Handles self-check/quiz creation, editing, and submissions.
 *
 * Supported Question Types:
 * - multiple_choice: Select one correct answer from options
 * - multiple_select: Select multiple correct answers (checkboxes)
 * - true_false: True or False statement
 * - fill_blank: Fill in missing words (supports multiple blanks)
 * - short_answer: Free-text response with keyword matching
 * - numeric: Number answer with tolerance range
 * - matching: Connect Column A items to Column B items
 * - ordering: Arrange items in correct sequence
 * - classification: Sort items into categories
 * - image_choice: Select correct answer from image options
 * - image_identification: "Name this picture" - type what you see
 * - hotspot: Click on specific area of image
 * - image_labeling: Label parts of a diagram
 * - audio_question: Listen to audio and answer
 * - video_question: Watch video and answer
 * - drag_drop: Drag items to target zones
 * - slider: Numeric slider for range answers
 */
class SelfCheckController extends Controller
{
    public function __construct(
        private SelfCheckGradingService $gradingService,
        private ProgressTrackingService $progressService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Create & Store
    |--------------------------------------------------------------------------
    */

    /**
     * Show the self-check creation form.
     */
    public function create(InformationSheet $informationSheet)
    {
        return view('modules.self-checks.create', compact('informationSheet'));
    }

    /**
     * Store a new self-check with questions.
     */
    public function store(StoreSelfCheckRequest $request, InformationSheet $informationSheet)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($request, $informationSheet) {
                // Calculate total points
                $totalPoints = 0;
                foreach ($request->questions as $q) {
                    $totalPoints += (int) ($q['points'] ?? 1);
                }

                $createData = [
                    'information_sheet_id' => $informationSheet->id,
                    'check_number' => $request->check_number,
                    'title' => $request->title,
                    'description' => $request->description,
                    'instructions' => $request->instructions,
                    'time_limit' => $request->time_limit,
                    'due_date' => $request->due_date,
                    'passing_score' => $request->passing_score ?? config('joms.grading.default_passing_score', 70),
                    'total_points' => $totalPoints,
                    'max_attempts' => $request->max_attempts,
                    'reveal_answers' => $request->has('reveal_answers'),
                    'randomize_questions' => $request->has('randomize_questions'),
                    'randomize_options' => $request->has('randomize_options'),
                ];

                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $createData['file_path'] = $file->storeAs('self-checks', $filename, 'public');
                    $createData['original_filename'] = $file->getClientOriginalName();

                    // Convert DOCX/PPTX to HTML for inline viewing
                    $ext = strtolower($file->getClientOriginalExtension());
                    if (in_array($ext, ['docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls', 'pdf'])) {
                        $conversionService = app(DocumentConversionService::class);
                        $fullPath = Storage::disk('public')->path($createData['file_path']);
                        $html = $conversionService->convertToHtml($fullPath, $ext);
                        if ($html) {
                            $createData['document_content'] = $html;
                        }
                    }
                }

                $selfCheck = SelfCheck::create($createData);

                $order = 0;
                foreach ($request->questions as $questionData) {
                    $order++;

                    // Process options based on question type
                    $options = $this->processQuestionOptions(
                        $questionData['question_type'],
                        $questionData['options'] ?? []
                    );

                    // Process correct answer
                    $correctAnswer = $this->processCorrectAnswer(
                        $questionData['question_type'],
                        $questionData['correct_answer'] ?? null,
                        $options
                    );

                    SelfCheckQuestion::create([
                        'self_check_id' => $selfCheck->id,
                        'question_text' => $questionData['question_text'],
                        'question_type' => $questionData['question_type'],
                        'points' => $questionData['points'],
                        'options' => $options,
                        'correct_answer' => $correctAnswer,
                        'explanation' => $questionData['explanation'] ?? null,
                        'order' => $order,
                    ]);
                }
            });

            if ($request->input('redirect') === 'continue') {
                return redirect()->route('self-checks.create', $informationSheet)
                    ->with('success', 'Self-check created! You can add another.');
            }

            return redirect()->route('content.management')
                ->with('success', 'Self-check created successfully!');
        } catch (\Exception $e) {
            Log::error('Self-check store failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to create self-check. Please try again.');
        }
    }

    /**
     * Process question options based on type.
     */
    private function processQuestionOptions(string $type, array $options): ?array
    {
        if (empty($options) && !in_array($type, ['true_false'])) {
            return null;
        }

        // Extract question_image (sent for most types via the image upload field)
        $questionImage = $options['question_image'] ?? null;
        unset($options['question_image']);

        $processed = null;

        switch ($type) {
            case 'multiple_choice':
                // Filter out empty options (only numeric-keyed string values)
                $processed = array_values(array_filter($options, fn($opt) => is_string($opt) && !empty(trim($opt))));
                break;

            case 'multiple_select':
                // Same as multiple_choice but allows multiple correct answers
                $processed = array_values(array_filter($options, fn($opt) => is_string($opt) && !empty(trim($opt))));
                break;

            case 'numeric':
                $processed = [
                    'tolerance' => floatval($options['tolerance'] ?? 0),
                    'unit' => $options['unit'] ?? null,
                    'decimal_places' => intval($options['decimal_places'] ?? 2),
                ];
                break;

            case 'classification':
                $categories = array_values(array_filter(
                    $options['categories'] ?? [],
                    fn($c) => is_string($c) && !empty(trim($c))
                ));
                $items = array_values(array_filter(
                    $options['items'] ?? [],
                    fn($i) => is_string($i) && !empty(trim($i))
                ));
                $processed = [
                    'categories' => $categories,
                    'items' => $items,
                    'item_categories' => $options['item_categories'] ?? [],
                ];
                break;

            case 'image_identification':
                $processed = [
                    'main_image' => $options['main_image'] ?? null,
                    'acceptable_answers' => array_map('trim', explode(',', $options['acceptable_answers'] ?? '')),
                ];
                break;

            case 'hotspot':
                $processed = [
                    'hotspot_image' => $options['hotspot_image'] ?? null,
                    'hotspot_x' => floatval($options['hotspot_x'] ?? 50),
                    'hotspot_y' => floatval($options['hotspot_y'] ?? 50),
                    'hotspot_radius' => floatval($options['hotspot_radius'] ?? 10),
                ];
                break;

            case 'image_labeling':
                $labels = array_values(array_filter(
                    $options['labels'] ?? [],
                    fn($l) => is_string($l) && !empty(trim($l))
                ));
                $processed = [
                    'label_image' => $options['label_image'] ?? null,
                    'labels' => $labels,
                    'label_positions' => $options['label_positions'] ?? [],
                ];
                break;

            case 'audio_question':
                $processed = [
                    'audio_url' => $options['audio_url'] ?? null,
                    'play_limit' => intval($options['play_limit'] ?? 0),
                    'response_type' => $options['response_type'] ?? 'text',
                    'mc_options' => isset($options['mc_options'])
                        ? array_values(array_filter($options['mc_options'], fn($o) => !empty(trim($o ?? ''))))
                        : null,
                ];
                break;

            case 'video_question':
                $processed = [
                    'video_url' => $options['video_url'] ?? null,
                    'start_time' => intval($options['start_time'] ?? 0),
                    'end_time' => !empty($options['end_time']) ? intval($options['end_time']) : null,
                    'response_type' => $options['response_type'] ?? 'text',
                    'mc_options' => isset($options['mc_options'])
                        ? array_values(array_filter($options['mc_options'], fn($o) => !empty(trim($o ?? ''))))
                        : null,
                ];
                break;

            case 'drag_drop':
                $draggables = array_values(array_filter(
                    $options['draggables'] ?? [],
                    fn($d) => is_string($d) && !empty(trim($d))
                ));
                $dropzones = array_values(array_filter(
                    $options['dropzones'] ?? [],
                    fn($d) => is_string($d) && !empty(trim($d))
                ));
                $processed = [
                    'draggables' => $draggables,
                    'dropzones' => $dropzones,
                    'correct_mapping' => $options['correct_mapping'] ?? [],
                ];
                break;

            case 'slider':
                $processed = [
                    'min' => floatval($options['min'] ?? 0),
                    'max' => floatval($options['max'] ?? 100),
                    'step' => floatval($options['step'] ?? 1),
                    'tolerance' => floatval($options['tolerance'] ?? 0),
                    'unit' => $options['unit'] ?? null,
                ];
                break;

            case 'matching':
                $pairs = [];
                $left = $options['left'] ?? [];
                $right = $options['right'] ?? [];
                for ($i = 0; $i < max(count($left), count($right)); $i++) {
                    if (!empty(trim($left[$i] ?? '')) && !empty(trim($right[$i] ?? ''))) {
                        $pairs[] = [
                            'left' => trim($left[$i]),
                            'right' => trim($right[$i]),
                        ];
                    }
                }
                $processed = ['pairs' => $pairs];
                break;

            case 'ordering':
                $items = array_filter($options, fn($opt) => is_string($opt) && !empty(trim($opt)));
                $processed = ['items' => array_values($items)];
                break;

            case 'image_choice':
                $imageOptions = [];
                $labels = $options['labels'] ?? [];
                $images = $options['images'] ?? [];
                for ($i = 0; $i < count($labels); $i++) {
                    if (!empty(trim($labels[$i] ?? ''))) {
                        $imageOptions[] = [
                            'label' => trim($labels[$i]),
                            'image' => trim($images[$i] ?? ''),
                        ];
                    }
                }
                $processed = $imageOptions;
                break;

            case 'short_answer':
                $processed = isset($options['model_answer']) ? ['model_answer' => $options['model_answer']] : null;
                break;

            case 'true_false':
                $processed = null;
                break;

            default:
                $processed = $options;
                break;
        }

        // Attach question_image to the result if present
        if ($questionImage && !empty(trim($questionImage))) {
            if ($processed === null) {
                $processed = [];
            }
            $processed['question_image'] = $questionImage;
        }

        return $processed;
    }

    /**
     * Process correct answer based on question type.
     */
    private function processCorrectAnswer(string $type, $answer, ?array $options): ?string
    {
        if ($answer === null && !in_array($type, ['matching', 'ordering', 'classification', 'drag_drop', 'hotspot', 'image_labeling'])) {
            return null;
        }

        switch ($type) {
            case 'multiple_choice':
            case 'image_choice':
                // Store the index as correct answer
                return (string) $answer;

            case 'multiple_select':
                // Store array of correct indices as JSON
                if (is_array($answer)) {
                    return json_encode(array_map('intval', $answer));
                }
                return $answer;

            case 'true_false':
                return $answer === 'true' || $answer === true ? 'true' : 'false';

            case 'fill_blank':
                // Store comma-separated acceptable answers
                return $answer;

            case 'numeric':
            case 'slider':
                // Store the numeric value
                return (string) floatval($answer);

            case 'matching':
                // For matching, correct answer is the pairs mapping (stored in options)
                return 'matching';

            case 'ordering':
                // For ordering, correct answer is the original order (stored in options)
                return 'ordering';

            case 'classification':
                // Correct mapping stored in options
                return 'classification';

            case 'image_identification':
                // Store comma-separated acceptable answers
                return $answer;

            case 'hotspot':
                // Correct coordinates stored in options
                return 'hotspot';

            case 'image_labeling':
                // Correct labels stored in options
                return 'labeling';

            case 'audio_question':
            case 'video_question':
                // Depends on response_type (text or multiple_choice)
                return $answer;

            case 'drag_drop':
                // Correct mapping stored in options
                return 'drag_drop';

            case 'short_answer':
                // Keywords for auto-grading
                return $answer;

            default:
                return $answer;
        }
    }

    /**
     * Upload an image for a question.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|mimetypes:image/jpeg,image/png,image/gif,image/webp|max:' . config('joms.uploads.max_image_size', 5120),
        ]);
        return $this->uploadMedia($request->file('image'), 'quiz-images', 'quiz_', 'image');
    }

    public function edit(InformationSheet $informationSheet, SelfCheck $selfCheck)
    {
        $selfCheck->load('questions');

        // Build structured question data for the JS quiz builder
        $existingQuestions = $selfCheck->questions->sortBy('order')->values()->map(function ($q) {
            $opts = $q->options ?? [];

            // Map stored options to the format the JS quiz builder expects
            $optionTexts = [];
            $metadata = [];

            // Extract question_image if present
            $questionImage = $opts['question_image'] ?? null;

            if (in_array($q->question_type, ['multiple_choice', 'multiple_select'])) {
                // MC options: only numeric-keyed values are choices
                $optionTexts = is_array($opts)
                    ? array_values(array_filter($opts, fn($v, $k) => is_int($k), ARRAY_FILTER_USE_BOTH))
                    : [];
            } else {
                // All other types store structured data that JS reads as "metadata"
                $metadata = is_array($opts) ? $opts : [];
            }

            return [
                'id' => $q->id,
                'question_type' => $q->question_type,
                'question_text' => $q->question_text,
                'points' => $q->points,
                'correct_answer' => $q->correct_answer,
                'explanation' => $q->explanation,
                'option_texts' => $optionTexts,
                'metadata' => $metadata,
                'question_image' => $questionImage,
            ];
        });

        return view('modules.self-checks.edit', compact('informationSheet', 'selfCheck', 'existingQuestions'));
    }

    public function update(UpdateSelfCheckRequest $request, InformationSheet $informationSheet, SelfCheck $selfCheck)
    {
        try {
            DB::transaction(function () use ($request, $selfCheck) {
                // Calculate total points
                $totalPoints = collect($request->questions)->sum('points');

                $updateData = [
                    'check_number' => $request->check_number,
                    'title' => $request->title,
                    'description' => $request->description,
                    'instructions' => $request->instructions,
                    'time_limit' => $request->time_limit,
                    'due_date' => $request->due_date,
                    'passing_score' => $request->passing_score,
                    'total_points' => $totalPoints,
                    'max_attempts' => $request->max_attempts,
                    'reveal_answers' => $request->has('reveal_answers'),
                    'randomize_questions' => $request->has('randomize_questions'),
                    'randomize_options' => $request->has('randomize_options'),
                ];

                if ($request->hasFile('file')) {
                    if ($selfCheck->file_path) {
                        Storage::disk('public')->delete($selfCheck->file_path);
                    }
                    $file = $request->file('file');
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $updateData['file_path'] = $file->storeAs('self-checks', $filename, 'public');
                    $updateData['original_filename'] = $file->getClientOriginalName();

                    // Convert DOCX/PPTX to HTML for inline viewing
                    $ext = strtolower($file->getClientOriginalExtension());
                    if (in_array($ext, ['docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls', 'pdf'])) {
                        $conversionService = app(DocumentConversionService::class);
                        $fullPath = Storage::disk('public')->path($updateData['file_path']);
                        $html = $conversionService->convertToHtml($fullPath, $ext);
                        $updateData['document_content'] = $html;
                    } else {
                        $updateData['document_content'] = null;
                    }
                }

                // Update self-check metadata
                $selfCheck->update($updateData);

                // Delete all existing questions (cascade deletes options)
                $selfCheck->questions()->delete();

                // Recreate questions from form data
                $order = 0;
                foreach ($request->questions as $questionData) {
                    $order++;

                    $options = $this->processQuestionOptions(
                        $questionData['question_type'],
                        $questionData['options'] ?? []
                    );

                    $correctAnswer = $this->processCorrectAnswer(
                        $questionData['question_type'],
                        $questionData['correct_answer'] ?? null,
                        $options
                    );

                    SelfCheckQuestion::create([
                        'self_check_id' => $selfCheck->id,
                        'question_text' => $questionData['question_text'],
                        'question_type' => $questionData['question_type'],
                        'points' => $questionData['points'],
                        'options' => $options,
                        'correct_answer' => $correctAnswer,
                        'explanation' => $questionData['explanation'] ?? null,
                        'order' => $order,
                    ]);
                }
            });

            return redirect()->route('content.management')
                ->with('success', 'Self-check updated successfully!');
        } catch (\Exception $e) {
            Log::error('Self-check update failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to update self-check. Please try again.');
        }
    }

    public function destroy(InformationSheet $informationSheet, SelfCheck $selfCheck)
    {
        try {
            if ($selfCheck->file_path) {
                Storage::disk('public')->delete($selfCheck->file_path);
            }
            $selfCheck->questions()->delete();
            $selfCheck->delete();

            return response()->json(['success' => 'Self-check deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Self-check destroy failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to delete self-check. Please try again.'], 500);
        }
    }

    public function download(SelfCheck $selfCheck)
    {
        if (!$selfCheck->file_path || !Storage::disk('public')->exists($selfCheck->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download(
            $selfCheck->file_path,
            $selfCheck->original_filename
        );
    }

    public function show(SelfCheck $selfCheck)
    {
        $selfCheck->load(['questions', 'informationSheet.module.course', 'submissions.user']);
        return view('modules.self-checks.show', compact('selfCheck'));
    }

    /**
     * Show self-check by module and information sheet
     */
    public function showBySheet(Course $course, Module $module, InformationSheet $informationSheet)
    {
        // Verify the information sheet belongs to this module
        if ($informationSheet->module_id !== $module->id) {
            abort(404);
        }

        // Get the self-check for this information sheet
        $selfCheck = SelfCheck::where('information_sheet_id', $informationSheet->id)->first();

        if (!$selfCheck) {
            return redirect()->route('courses.modules.show', [$module->course_id, $module])
                ->with('info', 'No self-check available for this information sheet yet.');
        }

        $selfCheck->load(['questions', 'informationSheet.module.course', 'submissions.user']);
        return view('modules.self-checks.show', compact('selfCheck'));
    }

    /*
    |--------------------------------------------------------------------------
    | Quiz Taking & Submission
    |--------------------------------------------------------------------------
    */

    /**
     * Submit quiz answers and calculate score.
     */
    public function submit(Request $request, SelfCheck $selfCheck)
    {
        $request->validate([
            'answers' => 'required|array',
        ]);

        // Check max attempts
        if ($selfCheck->max_attempts !== null) {
            $attemptCount = $selfCheck->submissions()->where('user_id', auth()->id())->count();
            if ($attemptCount >= $selfCheck->max_attempts) {
                return back()->with('error', 'You have reached the maximum number of attempts (' . $selfCheck->max_attempts . ') for this self-check.');
            }
        }

        try {
            $results = [];
            $submission = \DB::transaction(function () use ($request, $selfCheck, &$results) {
            $score = 0;
            $totalPoints = $selfCheck->total_points;

            foreach ($selfCheck->questions as $question) {
                $userAnswer = $request->answers[$question->id] ?? null;
                $isCorrect = $this->gradingService->gradeQuestion($question, $userAnswer);
                $pointsEarned = 0;

                if ($isCorrect === true) {
                    $pointsEarned = $question->points;
                    $score += $pointsEarned;
                } elseif (is_numeric($isCorrect)) {
                    // Partial credit (for matching/ordering)
                    $pointsEarned = round($question->points * $isCorrect, 2);
                    $score += $pointsEarned;
                }

                $results[] = [
                    'question' => $question,
                    'user_answer' => $userAnswer,
                    'is_correct' => $isCorrect === true,
                    'partial_credit' => is_numeric($isCorrect) ? $isCorrect : null,
                    'points_earned' => $pointsEarned,
                ];
            }

            $percentage = $this->gradingService->calculatePercentage($score, $totalPoints);
            $passed = $this->gradingService->isPassing($percentage, $selfCheck->passing_score);

            // Save submission to database
            $submission = $selfCheck->submissions()->create([
                'user_id' => auth()->id(),
                'score' => $score,
                'total_points' => $totalPoints,
                'percentage' => $percentage,
                'passed' => $passed,
                'answers' => $request->answers,
                'completed_at' => now(),
            ]);

            // Track progress
            $this->progressService->recordSelfCheckProgress($selfCheck, auth()->id(), [
                'score' => $score,
                'total_points' => $totalPoints,
                'percentage' => $percentage,
                'passed' => $passed,
            ]);

            // Award gamification points
            $gamification = app(\App\Services\GamificationService::class);
            $gamification->awardForActivity(auth()->user(), 'self_check_attempt', $submission);
            if ($passed) {
                $gamification->awardForActivity(auth()->user(), 'self_check_pass', $submission);
            }
            if ($percentage >= 100) {
                $gamification->awardForActivity(auth()->user(), 'perfect_score', $submission);
            }

            return $submission;
            }); // end DB::transaction

            // Re-extract values from submission for the response
            $score = $submission->score;
            $totalPoints = $submission->total_points;
            $percentage = $submission->percentage;
            $passed = $submission->passed;

            // Notify instructor of submission (outside transaction)
            $selfCheck->loadMissing('informationSheet.module.course.instructor');
            app(NotificationService::class)->notifySubmissionReceived(auth()->user(), 'self-check', $selfCheck);

            // Check if this is an AJAX request (focus mode)
            if ($request->expectsJson() || $request->input('focus_mode')) {
                return response()->json([
                    'success' => true,
                    'submission_id' => $submission->id,
                    'score' => $score,
                    'total_points' => $totalPoints,
                    'percentage' => $percentage,
                    'passed' => $passed,
                    'results' => collect($results)->map(function($r) {
                        return [
                            'question_id' => $r['question']->id,
                            'is_correct' => $r['is_correct'],
                            'points_earned' => $r['points_earned'],
                        ];
                    })->toArray(),
                ]);
            }

            // Store results in session and redirect (Post-Redirect-Get)
            session()->flash('sc_results', [
                'submission_id' => $submission->id,
                'results' => $results,
                'score' => $score,
                'totalPoints' => $totalPoints,
                'percentage' => $percentage,
                'passed' => $passed,
            ]);

            return redirect()->route('self-checks.results', $selfCheck);
        } catch (\Exception $e) {
            Log::error('Self-check submit failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson() || $request->input('focus_mode')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to submit self-check. Please try again.',
                ], 500);
            }

            return back()->withInput()->with('error', 'Failed to submit self-check. Please try again.');
        }
    }

    /**
     * Show quiz results after submission.
     */
    public function results(SelfCheck $selfCheck)
    {
        $selfCheck->load(['questions', 'informationSheet.module.course']);

        // Try session flash first (just submitted)
        $sessionResults = session('sc_results');

        if ($sessionResults && $sessionResults['submission_id']) {
            $submission = $selfCheck->submissions()->find($sessionResults['submission_id']);
            if ($submission) {
                $results = $sessionResults['results'];
                $score = $sessionResults['score'];
                $totalPoints = $sessionResults['totalPoints'];
                $percentage = $sessionResults['percentage'];
                $passed = $sessionResults['passed'];
                return view('modules.self-checks.results', compact(
                    'selfCheck', 'submission', 'results', 'score', 'totalPoints', 'percentage', 'passed'
                ));
            }
        }

        // Fallback: show latest submission for this user
        $submission = $selfCheck->submissions()
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        if (!$submission) {
            return redirect()->route('self-checks.show', $selfCheck)
                ->with('error', 'No submission found.');
        }

        $score = $submission->score;
        $totalPoints = $submission->total_points;
        $percentage = $submission->percentage;
        $passed = $submission->passed;
        $results = [];

        // Rebuild results from stored answers
        $storedAnswers = json_decode($submission->answers, true) ?? [];
        foreach ($selfCheck->questions as $question) {
            $userAnswer = $storedAnswers[$question->id] ?? null;
            $isCorrect = $this->gradingService->gradeQuestion($question, $userAnswer);
            $pointsEarned = 0;
            if ($isCorrect === true) {
                $pointsEarned = $question->points;
            } elseif (is_numeric($isCorrect)) {
                $pointsEarned = round($question->points * $isCorrect, 2);
            }
            $results[] = [
                'question' => $question,
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect === true,
                'partial_credit' => is_numeric($isCorrect) ? $isCorrect : null,
                'points_earned' => $pointsEarned,
            ];
        }

        return view('modules.self-checks.results', compact(
            'selfCheck', 'submission', 'results', 'score', 'totalPoints', 'percentage', 'passed'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Media Upload Endpoints
    |--------------------------------------------------------------------------
    */

    /**
     * Upload an audio file for audio questions.
     */
    public function uploadAudio(Request $request)
    {
        $request->validate([
            'audio' => 'required|mimes:mp3,wav,ogg,m4a,webm|mimetypes:audio/mpeg,audio/wav,audio/ogg,audio/mp4,audio/x-m4a,audio/webm|max:' . config('joms.uploads.max_audio_size', 20480),
        ]);
        return $this->uploadMedia($request->file('audio'), 'quiz-audio', 'quiz_audio_', 'audio');
    }

    /**
     * Upload a video file for video questions.
     */
    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|mimes:mp4,webm,ogg,mov|mimetypes:video/mp4,video/webm,video/ogg,video/quicktime|max:' . config('joms.uploads.max_video_size', 102400),
        ]);
        return $this->uploadMedia($request->file('video'), 'quiz-video', 'quiz_video_', 'video');
    }

    private function uploadMedia(UploadedFile $file, string $folder, string $prefix, string $type): \Illuminate\Http\JsonResponse
    {
        try {
            $filename = $prefix . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $filename, 'public');

            return response()->json([
                'success' => true,
                'url' => '/storage/' . $path,
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            Log::error("Self-check {$type} upload failed", [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return response()->json(['error' => "Failed to upload {$type}. Please try again."], 500);
        }
    }
}
