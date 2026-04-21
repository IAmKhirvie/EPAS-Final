<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use App\Models\Module;
use App\Models\InformationSheet;
use App\Models\Course;
use App\Models\Topic;
use App\Models\UserProgress;
use App\Models\SelfCheck;
use App\Services\ModuleService;
use App\Services\PrerequisiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;


class ModuleController extends Controller
{
    protected ModuleService $moduleService;
    protected PrerequisiteService $prerequisiteService;

    public function __construct(ModuleService $moduleService, PrerequisiteService $prerequisiteService)
    {
        $this->moduleService = $moduleService;
        $this->prerequisiteService = $prerequisiteService;
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('create', Module::class);

        $validated = $request->validate([
            'qualification_title' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'unit_of_competency' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'module_title' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'module_number' => 'required|string|max:50',
            'module_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'order' => 'nullable|integer|min:1',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'table_of_contents' => 'nullable|string',
            'how_to_use_cblm' => 'nullable|string',
            'introduction' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            // Final Assessment fields
            'require_final_assessment' => 'boolean',
            'assessment_question_mode' => 'nullable|in:all,random_subset',
            'assessment_question_count' => 'nullable|integer|min:1',
            'assessment_passing_score' => 'nullable|integer|min:0|max:100',
            'assessment_time_limit' => 'nullable|integer|min:1',
            'assessment_max_attempts' => 'nullable|integer|min:1',
            'assessment_randomize_questions' => 'boolean',
            'assessment_show_answers' => 'boolean',
            'assessment_require_completion' => 'boolean',
        ]);

        // Handle boolean checkboxes (they're not sent when unchecked)
        $validated['is_active'] = $request->has('is_active');
        $validated['require_final_assessment'] = $request->has('require_final_assessment');
        $validated['assessment_randomize_questions'] = $request->has('assessment_randomize_questions');
        $validated['assessment_show_answers'] = $request->has('assessment_show_answers');
        $validated['assessment_require_completion'] = $request->has('assessment_require_completion');

        // Strip MS Word HTML bloat from rich text fields
        $sanitizer = app(\App\Services\ContentSanitizationService::class);
        foreach (['introduction', 'how_to_use_cblm', 'learning_outcomes', 'table_of_contents'] as $field) {
            if (!empty($validated[$field])) {
                $validated[$field] = $sanitizer->stripWordBloat($validated[$field]);
            }
        }

        try {
            $module = DB::transaction(function () use ($validated, $course, $request) {
                $thumbnailPath = null;
                if ($request->hasFile('thumbnail')) {
                    $thumbnailPath = $request->file('thumbnail')->store('modules/thumbnails', 'public');
                }

                // Determine order: use provided value or auto-increment
                $order = $validated['order'] ?? (Module::where('course_id', $course->id)->max('order') + 1);

                // If order is specified and conflicts, shift existing modules
                if (isset($validated['order'])) {
                    Module::where('course_id', $course->id)
                        ->where('order', '>=', $order)
                        ->increment('order');
                }

                return Module::create([
                    'course_id' => $course->id,
                    'qualification_title' => $validated['qualification_title'],
                    'unit_of_competency' => $validated['unit_of_competency'],
                    'module_title' => $validated['module_title'],
                    'module_number' => $validated['module_number'],
                    'module_name' => $validated['module_name'],
                    'thumbnail' => $thumbnailPath,
                    'table_of_contents' => $validated['table_of_contents'],
                    'how_to_use_cblm' => $validated['how_to_use_cblm'],
                    'introduction' => $validated['introduction'],
                    'learning_outcomes' => $validated['learning_outcomes'],
                    'is_active' => true,
                    'order' => $order,
                    // Final Assessment fields
                    'require_final_assessment' => $validated['require_final_assessment'],
                    'assessment_question_mode' => $validated['assessment_question_mode'] ?? 'all',
                    'assessment_question_count' => $validated['assessment_question_count'] ?? null,
                    'assessment_passing_score' => $validated['assessment_passing_score'] ?? 70,
                    'assessment_time_limit' => $validated['assessment_time_limit'] ?? null,
                    'assessment_max_attempts' => $validated['assessment_max_attempts'] ?? null,
                    'assessment_randomize_questions' => $validated['assessment_randomize_questions'],
                    'assessment_show_answers' => $validated['assessment_show_answers'],
                    'assessment_require_completion' => $validated['assessment_require_completion'],
                ]);
            });

            return redirect()->route('courses.show', $course)
                ->with('success', 'Module created successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Module creation failed: ' . $e->getMessage());

            // Check for duplicate entry error
            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()
                    ->with('error', 'A module with a similar title already exists in this course. Please use a different module title.');
            }

            return back()->withInput()
                ->with('error', 'Failed to create module due to a database error. Please try again.');
        } catch (\Exception $e) {
            Log::error('Module creation failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to create module. Please try again.');
        }
    }

    public function show(Course $course, Module $module)
    {
        $this->verifyModuleBelongsToCourse($course, $module);
        $this->authorize('view', $course);

        $user = Auth::user();

        // Check prerequisites for students
        if ($user && $user->role === Roles::STUDENT) {
            $unmetPrerequisites = $this->prerequisiteService->getUnmetPrerequisites($user, $module);

            if ($unmetPrerequisites->isNotEmpty()) {
                return view('modules.locked', [
                    'module' => $module,
                    'course' => $course,
                    'unmetPrerequisites' => $unmetPrerequisites,
                ]);
            }
        }

        $module->load([
            'informationSheets.selfChecks',
            'informationSheets.taskSheets',
            'informationSheets.jobSheets',
            'informationSheets.documentAssessments',
            'informationSheets.topics',
            'course',
            'prerequisites.prerequisiteModule',
        ]);

        // Get progress for logged-in users
        $progress = null;
        $sheetCompletion = [];
        if ($user) {
            $progress = $this->moduleService->getProgress($module, $user->id);

            // Get completion status for each information sheet (for sequential locking)
            foreach ($module->informationSheets as $sheet) {
                $sheetCompletion[$sheet->id] = \App\Models\UserProgress::where('user_id', $user->id)
                    ->where('module_id', $module->id)
                    ->where('progressable_type', \App\Models\InformationSheet::class)
                    ->where('progressable_id', $sheet->id)
                    ->where('status', 'completed')
                    ->exists();
            }
        }

        return view('modules.show-unified', compact('module', 'course', 'progress', 'sheetCompletion'));
    }

    public function showInformationSheet(Course $course, Module $module, InformationSheet $informationSheet)
    {
        // Redirect to the unified module page
        return redirect()->route('courses.modules.show', [$course, $module]);
    }

    public function getContent(Course $course, Module $module, $contentType)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        try {
            $viewMap = [
                'introduction' => 'modules.content.introduction',
                'electric-history' => 'modules.content.electric-history',
                'static-electricity' => 'modules.content.static-electricity',
                'free-electrons' => 'modules.content.free-electrons',
                'alternative-energy' => 'modules.content.alternative-energy',
                'electric-energy' => 'modules.content.electric-energy',
                'materials' => 'modules.content.materials',
                'self-check' => 'modules.content.self-check'
            ];

            if (!array_key_exists($contentType, $viewMap)) {
                return response()->json(['error' => 'Content type not found'], 404);
            }

            return response()->json([
                'html' => view($viewMap[$contentType], compact('module'))->render()
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading module content: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load module content'
            ], 500);
        }
    }

    public function showTopic(Course $course, Module $module, InformationSheet $informationSheet, Topic $topic)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        if (
            $topic->information_sheet_id !== $informationSheet->id ||
            $informationSheet->module_id !== $module->id
        ) {
            abort(404);
        }

        $this->moduleService->trackTopicProgress($topic);

        return view('modules.topics.show', [
            'module' => $module,
            'course' => $course,
            'informationSheet' => $informationSheet,
            'topic' => $topic,
            'nextTopic' => $topic->getNextTopic(),
            'prevTopic' => $topic->getPreviousTopic()
        ]);
    }

    public function getTopicContent(Course $course, Module $module, InformationSheet $informationSheet, Topic $topic)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        if (
            $topic->information_sheet_id !== $informationSheet->id ||
            $informationSheet->module_id !== $module->id
        ) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        try {
            $html = view('modules.information-sheets.topics.content-partial', [
                'topic' => $topic,
            ])->render();

            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            Log::error('Failed to load topic content: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load topic.']);
        }
    }

    public function create(Course $course)
    {
        $user = Auth::user();

        // Verify instructor owns this course
        if ($user->role === Roles::INSTRUCTOR && $course->instructor_id !== $user->id) {
            abort(403);
        }

        $courses = Course::where('is_active', true)
            ->when($user->role === Roles::INSTRUCTOR, fn($q) => $q->where('instructor_id', $user->id))
            ->orderBy('course_name')
            ->get();

        return view('modules.create', compact('courses', 'course'));
    }

    public function getModuleProgress(Course $course, Module $module)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        if (!auth()->check()) {
            return response()->json([
                'percentage' => 0,
                'completed' => 0,
                'total' => 0,
                'completed_items' => 0,
                'total_items' => 0,
                'status' => 'not_started'
            ]);
        }

        $progress = $this->moduleService->getProgress($module, auth()->id());
        return response()->json($progress);
    }

    /**
     * Mark a topic as completed (called when user scrolls to the bottom).
     */
    public function markTopicComplete(Course $course, Module $module, InformationSheet $informationSheet, Topic $topic)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        if (
            $topic->information_sheet_id !== $informationSheet->id ||
            $informationSheet->module_id !== $module->id
        ) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        try {
            // Track the topic as viewed/completed
            $this->moduleService->trackTopicProgress($topic);

            // Award gamification points for topic completion
            app(\App\Services\GamificationService::class)->awardForActivity(auth()->user(), 'topic_complete', $topic);

            // Get updated progress
            $progress = $this->moduleService->getProgress($module, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Topic marked as complete',
                'progress' => $progress
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark topic complete: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update progress.']);
        }
    }

    public function edit(Course $course, Module $module)
    {
        $this->verifyModuleBelongsToCourse($course, $module);
        $this->authorize('update', $module);
        $user = Auth::user();

        $courses = Course::where('is_active', true)
            ->when($user->role === Roles::INSTRUCTOR, fn($q) => $q->where('instructor_id', $user->id))
            ->orderBy('course_name')
            ->get();

        // Get available modules for prerequisites (same course, excluding self)
        $availablePrerequisites = Module::where('course_id', $course->id)
            ->where('id', '!=', $module->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Get current prerequisites
        $currentPrerequisites = $module->prerequisites()
            ->pluck('prerequisite_module_id')
            ->toArray();

        return view('modules.edit', compact('module', 'courses', 'course', 'availablePrerequisites', 'currentPrerequisites'));
    }

    public function update(Request $request, Course $course, Module $module)
    {
        $this->verifyModuleBelongsToCourse($course, $module);
        $this->authorize('update', $module);

        $validated = $request->validate([
            'qualification_title' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'unit_of_competency' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'module_title' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'module_number' => 'required|string|max:50',
            'module_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'remove_thumbnail' => 'nullable|boolean',
            'table_of_contents' => 'nullable|string',
            'how_to_use_cblm' => 'nullable|string',
            'introduction' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'exists:modules,id',
            // Assessment fields
            'require_final_assessment' => 'boolean',
            'assessment_randomize_questions' => 'boolean',
            'assessment_show_answers' => 'boolean',
            'assessment_passing_score' => 'nullable|integer|min:0|max:100',
            'assessment_time_limit' => 'nullable|integer|min:1',
            'assessment_max_attempts' => 'nullable|integer|min:1',
            'assessment_question_count' => 'nullable|integer|min:1',
            'assessment_question_mode' => 'nullable|in:all,random_subset',
            'assessment_require_completion' => 'boolean',
        ]);

        // Handle boolean checkboxes (they're not sent when unchecked)
        $validated['is_active'] = $request->has('is_active');
        $validated['require_final_assessment'] = $request->has('require_final_assessment');
        $validated['assessment_randomize_questions'] = $request->has('assessment_randomize_questions');
        $validated['assessment_show_answers'] = $request->has('assessment_show_answers');
        $validated['assessment_require_completion'] = $request->has('assessment_require_completion');

        // Strip MS Word HTML bloat from rich text fields
        $sanitizer = app(\App\Services\ContentSanitizationService::class);
        foreach (['introduction', 'how_to_use_cblm', 'learning_outcomes', 'table_of_contents'] as $field) {
            if (!empty($validated[$field])) {
                $validated[$field] = $sanitizer->stripWordBloat($validated[$field]);
            }
        }

        try {
            DB::transaction(function () use ($validated, $module, $request) {
                // Handle thumbnail upload
                if ($request->hasFile('thumbnail')) {
                    // Delete old thumbnail if exists
                    if ($module->thumbnail) {
                        Storage::disk('public')->delete($module->thumbnail);
                    }
                    $validated['thumbnail'] = $request->file('thumbnail')->store('modules/thumbnails', 'public');
                } elseif ($request->boolean('remove_thumbnail') && $module->thumbnail) {
                    Storage::disk('public')->delete($module->thumbnail);
                    $validated['thumbnail'] = null;
                } else {
                    unset($validated['thumbnail']);
                }
                unset($validated['remove_thumbnail']);

                $module->update($validated);

                // Update prerequisites if provided
                if ($request->has('prerequisites')) {
                    $this->prerequisiteService->syncPrerequisites(
                        $module,
                        $request->input('prerequisites', [])
                    );
                } else {
                    // Clear prerequisites if none selected
                    $this->prerequisiteService->syncPrerequisites($module, []);
                }
            });

            return redirect()->route('courses.show', $module->course_id)
                ->with('success', 'Module updated successfully!');
        } catch (\InvalidArgumentException $e) {
            // Circular dependency error
            return back()->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Module update failed: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());

            return back()->withInput()
                ->with('error', 'Failed to update module. Please try again.');
        }
    }

    public function destroy(Course $course, Module $module)
    {
        $this->verifyModuleBelongsToCourse($course, $module);
        $this->authorize('delete', $module);

        try {
            $courseId = $module->course_id;
            // Cascade soft-delete handled by Module::deleting() boot method
            $module->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Module deleted successfully!'
                ]);
            }

            return redirect()->route('courses.show', $courseId)
                ->with('success', 'Module deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Module deletion failed: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to delete module. Please try again.'
                ], 500);
            }

            return back()->with('error', 'Failed to delete module. Please try again.');
        }
    }

    public function uploadImage(Request $request, Course $course, Module $module)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|mimetypes:image/jpeg,image/png,image/gif,image/webp|max:' . config('joms.uploads.max_image_size', 5120),
            'caption' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:100',
        ]);

        try {
            $this->moduleService->uploadImage($module, $request->image, $request->caption, $request->section);
            return redirect()->back()->with('success', 'Image uploaded successfully!');
        } catch (\Exception $e) {
            Log::error('Module image upload failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to upload image. Please try again.');
        }
    }

    public function deleteImage(Request $request, Course $course, Module $module, $imageIndex)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        try {
            $this->moduleService->deleteImage($module, (int) $imageIndex);
            return redirect()->back()->with('success', 'Image deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Module image deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete image. Please try again.');
        }
    }

    public function getSheetContent(Course $course, Module $module, InformationSheet $informationSheet)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        try {
            $informationSheet->load(['topics', 'selfChecks', 'taskSheets', 'jobSheets']);

            $html = view('modules.information-sheets.content-partial', [
                'sheet' => $informationSheet,
                'course' => $course,
                'module' => $module,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'sheet' => [
                    'id' => $informationSheet->id,
                    'title' => $informationSheet->title,
                    'sheet_number' => $informationSheet->sheet_number,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load sheet content: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load content. Please try again.',
                'html' => '<div class="alert alert-danger">Failed to load content. Please refresh the page.</div>'
            ]);
        }
    }

    public function getSelfCheckContent(Course $course, Module $module, InformationSheet $informationSheet)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        try {
            $informationSheet->load(['selfChecks.questions']);
            $selfCheck = $informationSheet->selfChecks->first();

            if (!$selfCheck) {
                return response()->json([
                    'success' => false,
                    'html' => '<div class="alert alert-info">No self-check available for this information sheet.</div>'
                ]);
            }

            $html = view('modules.partials.self-check-inline', [
                'selfCheck' => $selfCheck,
                'informationSheet' => $informationSheet,
                'module' => $module,
                'course' => $course,
            ])->render();

            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            Log::error('Failed to load self-check content: ' . $e->getMessage());
            return response()->json(['success' => false, 'html' => '<div class="alert alert-danger">Failed to load self-check.</div>']);
        }
    }

    public function getTaskSheetContent(Course $course, Module $module, InformationSheet $informationSheet)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        try {
            $informationSheet->load(['taskSheets.items']);
            $taskSheet = $informationSheet->taskSheets->first();

            if (!$taskSheet) {
                return response()->json([
                    'success' => false,
                    'html' => '<div class="alert alert-info">No task sheet available for this information sheet.</div>'
                ]);
            }

            $html = view('modules.partials.task-sheet-inline', [
                'taskSheet' => $taskSheet,
                'informationSheet' => $informationSheet,
                'module' => $module,
                'course' => $course,
            ])->render();

            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            Log::error('Failed to load task sheet content: ' . $e->getMessage());
            return response()->json(['success' => false, 'html' => '<div class="alert alert-danger">Failed to load task sheet.</div>']);
        }
    }

    public function getJobSheetContent(Course $course, Module $module, InformationSheet $informationSheet)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        try {
            $informationSheet->load(['jobSheets.steps']);
            $jobSheet = $informationSheet->jobSheets->first();

            if (!$jobSheet) {
                return response()->json([
                    'success' => false,
                    'html' => '<div class="alert alert-info">No job sheet available for this information sheet.</div>'
                ]);
            }

            $html = view('modules.partials.job-sheet-inline', [
                'jobSheet' => $jobSheet,
                'informationSheet' => $informationSheet,
                'module' => $module,
                'course' => $course,
            ])->render();

            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            Log::error('Failed to load job sheet content: ' . $e->getMessage());
            return response()->json(['success' => false, 'html' => '<div class="alert alert-danger">Failed to load job sheet.</div>']);
        }
    }

    public function downloadPdf(Course $course, Module $module)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        $module->load([
            'informationSheets.topics',
            'informationSheets.selfChecks.questions',
            'informationSheets.taskSheets',
            'informationSheets.jobSheets',
        ]);

        $exportDate = now()->format('F j, Y g:i A');

        $html = view('exports.module-pdf', compact('module', 'exportDate'))->render();

        $filename = \Illuminate\Support\Str::slug($module->module_name) . '-' . date('Y-m-d') . '.html';

        return Response::make($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function printPreview(Course $course, Module $module)
    {
        $this->verifyModuleBelongsToCourse($course, $module);

        $module->load([
            'informationSheets' => fn($q) => $q->orderBy('sheet_number'),
            'informationSheets.topics' => fn($q) => $q->orderBy('order'),
            'informationSheets.selfChecks.questions' => fn($q) => $q->orderBy('order'),
            'informationSheets.taskSheets',
            'informationSheets.jobSheets',
        ]);

        $exportDate = now()->format('F j, Y g:i A');

        return view('exports.module-pdf', compact('module', 'exportDate'));
    }

    private function verifyModuleBelongsToCourse(Course $course, Module $module): void
    {
        if ($module->course_id !== $course->id) {
            abort(404);
        }
    }
}
