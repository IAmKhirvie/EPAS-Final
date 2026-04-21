<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use App\Models\InformationSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Course::where('is_active', true)
            ->withCount(['modules' => function ($query) {
                $query->where('is_active', true);
            }])
            ->with(['instructor', 'category']);

        // Instructors only see their assigned courses
        if ($user->role === Roles::INSTRUCTOR) {
            $query->where('instructor_id', $user->id);
        }

        // Students only see courses targeting their section
        if ($user->role === Roles::STUDENT) {
            $query->forSection($user->section);
        }

        $courses = $query->orderBy('order')->get();
        $categories = CourseCategory::active()->ordered()->get();

        // Gather calendar events for the mini calendar
        $calendarEvents = $this->getCalendarEvents($courses);

        return view('courses.index', compact('courses', 'categories', 'calendarEvents'));
    }

    /**
     * Get calendar events from courses, self-checks, and other deadlines
     */
    /**
     * Color map for calendar event types.
     */
    private const EVENT_COLORS = [
        'course_start'        => '#4cc9f0',
        'course_end'          => '#4cc9f0',
        'self_check'          => '#f72585',
        'homework'            => '#7209b7',
        'competency_test'     => '#ffb902',
        'document_assessment' => '#06d6a0',
    ];

    private function getCalendarEvents($courses)
    {
        $events = [];

        foreach ($courses as $course) {
            // Course start date
            if ($course->start_date) {
                $events[] = [
                    'date' => $course->start_date->format('Y-m-d'),
                    'title' => $course->course_code . ' Starts',
                    'type' => 'course_start',
                    'color' => self::EVENT_COLORS['course_start'],
                    'course_id' => $course->id,
                ];
            }

            // Course end date
            if ($course->end_date) {
                $events[] = [
                    'date' => $course->end_date->format('Y-m-d'),
                    'title' => $course->course_code . ' Ends',
                    'type' => 'course_end',
                    'color' => self::EVENT_COLORS['course_end'],
                    'course_id' => $course->id,
                ];
            }

            // Get self-check due dates through modules and information sheets
            $selfCheckDueDates = DB::table('self_checks')
                ->join('information_sheets', 'self_checks.information_sheet_id', '=', 'information_sheets.id')
                ->join('modules', 'information_sheets.module_id', '=', 'modules.id')
                ->where('modules.course_id', $course->id)
                ->whereNotNull('self_checks.due_date')
                ->whereNull('self_checks.deleted_at')
                ->whereNull('information_sheets.deleted_at')
                ->whereNull('modules.deleted_at')
                ->select('self_checks.due_date', 'self_checks.title')
                ->get();

            foreach ($selfCheckDueDates as $selfCheck) {
                $events[] = [
                    'date' => \Carbon\Carbon::parse($selfCheck->due_date)->format('Y-m-d'),
                    'title' => $selfCheck->title,
                    'type' => 'self_check',
                    'color' => self::EVENT_COLORS['self_check'],
                    'course_id' => $course->id,
                ];
            }

            // Get homework due dates
            $homeworkDueDates = DB::table('homeworks')
                ->join('information_sheets', 'homeworks.information_sheet_id', '=', 'information_sheets.id')
                ->join('modules', 'information_sheets.module_id', '=', 'modules.id')
                ->where('modules.course_id', $course->id)
                ->whereNotNull('homeworks.due_date')
                ->whereNull('homeworks.deleted_at')
                ->whereNull('information_sheets.deleted_at')
                ->whereNull('modules.deleted_at')
                ->select('homeworks.due_date', 'homeworks.title')
                ->get();

            foreach ($homeworkDueDates as $homework) {
                $events[] = [
                    'date' => \Carbon\Carbon::parse($homework->due_date)->format('Y-m-d'),
                    'title' => $homework->title,
                    'type' => 'homework',
                    'color' => self::EVENT_COLORS['homework'],
                    'course_id' => $course->id,
                ];
            }

            // Get competency test due dates
            $competencyTestDueDates = DB::table('competency_tests')
                ->join('modules', 'competency_tests.module_id', '=', 'modules.id')
                ->where('modules.course_id', $course->id)
                ->whereNotNull('competency_tests.due_date')
                ->whereNull('competency_tests.deleted_at')
                ->whereNull('modules.deleted_at')
                ->select('competency_tests.due_date', 'competency_tests.title')
                ->get();

            foreach ($competencyTestDueDates as $test) {
                $events[] = [
                    'date' => \Carbon\Carbon::parse($test->due_date)->format('Y-m-d'),
                    'title' => $test->title,
                    'type' => 'competency_test',
                    'color' => self::EVENT_COLORS['competency_test'],
                    'course_id' => $course->id,
                ];
            }

            // Get document assessment due dates
            $docAssessmentDueDates = DB::table('document_assessments')
                ->join('information_sheets', 'document_assessments.information_sheet_id', '=', 'information_sheets.id')
                ->join('modules', 'information_sheets.module_id', '=', 'modules.id')
                ->where('modules.course_id', $course->id)
                ->whereNotNull('document_assessments.due_date')
                ->whereNull('document_assessments.deleted_at')
                ->whereNull('information_sheets.deleted_at')
                ->whereNull('modules.deleted_at')
                ->select('document_assessments.due_date', 'document_assessments.title')
                ->get();

            foreach ($docAssessmentDueDates as $docAssessment) {
                $events[] = [
                    'date' => \Carbon\Carbon::parse($docAssessment->due_date)->format('Y-m-d'),
                    'title' => $docAssessment->title,
                    'type' => 'document_assessment',
                    'color' => self::EVENT_COLORS['document_assessment'],
                    'course_id' => $course->id,
                ];
            }
        }

        return $events;
    }

    public function contentManagement()
    {
        $user = Auth::user();

        $query = Course::with([
            'modules' => function ($query) {
                $query->orderBy('order');
            },
            'modules.informationSheets' => function ($query) {
                $query->orderBy('sheet_number')
                    ->with(['topics', 'selfChecks.questions', 'taskSheets', 'jobSheets', 'homeworks', 'checklists']);
            },
            'instructor'
        ]);

        // Instructors only see their assigned courses
        if ($user->role === Roles::INSTRUCTOR) {
            $query->where('instructor_id', $user->id);
        }

        $courses = $query->orderBy('order')->get();

        // Get instructors list for admin to assign
        $instructors = $user->role === Roles::ADMIN
            ? User::where('role', Roles::INSTRUCTOR)->where('stat', 1)->orderBy('last_name')->get()
            : collect();

        return view('content-management.index', compact('courses', 'instructors'));
    }

    public function create()
    {
        $this->authorize('create', Course::class);

        $instructors = User::where('role', Roles::INSTRUCTOR)
            ->where('stat', 1)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        $sections = User::whereNotNull('section')->where('section', '!=', '')->distinct()->pluck('section')->sort()->values();
        $categories = CourseCategory::active()->ordered()->get();

        return view('courses.create', compact('instructors', 'sections', 'categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'course_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'course_code' => 'required|string|max:50|unique:courses',
            'description' => 'nullable|string',
            'sector' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:course_categories,id',
            'new_category' => 'nullable|string|max:100',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'instructor_id' => 'nullable|exists:users,id',
            'target_sections' => 'nullable|string|max:500',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'schedule_days' => 'nullable|string|max:100',
            'schedule_time_start' => 'nullable|date_format:H:i',
            'schedule_time_end' => 'nullable|date_format:H:i|after:schedule_time_start',
            'duration_hours' => 'nullable|integer|min:1',
        ]);

        try {
            // Handle new category creation
            $categoryId = $validated['category_id'] ?? null;
            if (!empty($validated['new_category'])) {
                $newCategory = CourseCategory::create([
                    'name' => $validated['new_category'],
                    'slug' => Str::slug($validated['new_category']),
                    'color' => $this->generateCategoryColor(),
                    'icon' => 'fas fa-folder',
                    'order' => CourseCategory::max('order') + 1,
                ]);
                $categoryId = $newCategory->id;
            }

            // Handle thumbnail upload
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('course-thumbnails', 'public');
            }

            $course = Course::create([
                'course_name' => $validated['course_name'],
                'course_code' => $validated['course_code'],
                'description' => $validated['description'] ?? null,
                'sector' => $validated['sector'] ?? null,
                'category_id' => $categoryId,
                'thumbnail' => $thumbnailPath,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'schedule_days' => $validated['schedule_days'] ?? null,
                'schedule_time_start' => $validated['schedule_time_start'] ?? null,
                'schedule_time_end' => $validated['schedule_time_end'] ?? null,
                'duration_hours' => $validated['duration_hours'] ?? null,
                'instructor_id' => $validated['instructor_id'] ?? null,
                'target_sections' => $validated['target_sections'] ?? null,
                'is_active' => true,
                'order' => Course::max('order') + 1,
            ]);

            return redirect()->route('courses.show', $course->id)
                ->with('success', 'Course created successfully!');
        } catch (\Exception $e) {
            Log::error('Course creation failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to create course. Please try again.');
        }
    }

    /**
     * Generate a random category color from a preset palette
     */
    private function generateCategoryColor(): string
    {
        $colors = [
            '#ef4444', // Red
            '#f97316', // Orange
            '#f59e0b', // Amber
            '#eab308', // Yellow
            '#84cc16', // Lime
            '#22c55e', // Green
            '#10b981', // Emerald
            '#14b8a6', // Teal
            '#06b6d4', // Cyan
            '#0ea5e9', // Sky
            '#6d9773', // Green (Theme)
            '#6366f1', // Indigo
            '#8b5cf6', // Violet
            '#a855f7', // Purple
            '#d946ef', // Fuchsia
            '#ec4899', // Pink
            '#f43f5e', // Rose
        ];

        // Get already used colors
        $usedColors = CourseCategory::pluck('color')->toArray();

        // Try to find an unused color
        $availableColors = array_diff($colors, $usedColors);

        if (!empty($availableColors)) {
            return $availableColors[array_rand($availableColors)];
        }

        // If all colors are used, return a random one
        return $colors[array_rand($colors)];
    }

    public function show(Course $course)
    {
        $this->authorize('view', $course);
        $user = Auth::user();

        $course->load(['modules' => function ($query) {
            $query->where('is_active', true)->orderBy('order');
        }, 'instructor']);

        $canEdit = $user->role === Roles::ADMIN || $course->instructor_id === $user->id;

        return view('courses.show', compact('course', 'canEdit'));
    }

    public function edit(Course $course)
    {
        $this->authorize('update', $course);
        $user = Auth::user();

        $instructors = $user->role === Roles::ADMIN
            ? User::where('role', Roles::INSTRUCTOR)->where('stat', 1)->orderBy('last_name')->get()
            : collect();

        $sections = User::whereNotNull('section')->where('section', '!=', '')->distinct()->pluck('section')->sort()->values();
        $categories = CourseCategory::active()->ordered()->get();

        return view('courses.edit', compact('course', 'instructors', 'sections', 'categories'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        $user = Auth::user();

        $rules = [
            'course_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'course_code' => 'required|string|max:50|unique:courses,course_code,' . $course->id,
            'description' => 'nullable|string',
            'sector' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:course_categories,id',
            'new_category' => 'nullable|string|max:100',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_thumbnail' => 'nullable|boolean',
            'is_active' => 'boolean',
            'target_sections' => 'nullable|string|max:500',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'schedule_days' => 'nullable|string|max:100',
            'schedule_time_start' => 'nullable|date_format:H:i',
            'schedule_time_end' => 'nullable|date_format:H:i',
            'duration_hours' => 'nullable|integer|min:1',
        ];

        // Only admin can change instructor assignment
        if ($user->role === Roles::ADMIN) {
            $rules['instructor_id'] = 'nullable|exists:users,id';
        }

        $validated = $request->validate($rules);

        try {
            // Handle new category creation
            if (!empty($validated['new_category'])) {
                $newCategory = CourseCategory::create([
                    'name' => $validated['new_category'],
                    'slug' => Str::slug($validated['new_category']),
                    'color' => $this->generateCategoryColor(),
                    'icon' => 'fas fa-folder',
                    'order' => CourseCategory::max('order') + 1,
                ]);
                $validated['category_id'] = $newCategory->id;
            }

            // Handle thumbnail
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail
                if ($course->thumbnail) {
                    Storage::disk('public')->delete($course->thumbnail);
                }
                $validated['thumbnail'] = $request->file('thumbnail')->store('course-thumbnails', 'public');
            } elseif ($request->boolean('remove_thumbnail') && $course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
                $validated['thumbnail'] = null;
            }

            // Remove non-fillable fields
            unset($validated['new_category'], $validated['remove_thumbnail']);

            $course->update($validated);

            return redirect()->route('courses.show', $course->id)
                ->with('success', 'Course updated successfully!');
        } catch (\Exception $e) {
            Log::error('Course update failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to update course. Please try again.');
        }
    }

    /**
     * Assign an instructor to a course (Admin only)
     */
    public function assignInstructor(Request $request, Course $course)
    {
        $this->authorizeAdmin('Only administrators can assign instructors.');

        $request->validate([
            'instructor_id' => 'nullable|exists:users,id',
        ]);

        try {
            $course->update(['instructor_id' => $request->instructor_id]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $request->instructor_id
                        ? 'Instructor assigned successfully.'
                        : 'Instructor removed from course.',
                ]);
            }

            return back()->with('success', 'Instructor updated successfully.');
        } catch (\Exception $e) {
            Log::error('Instructor assignment failed', [
                'error' => $e->getMessage(),
                'course_id' => $course->id,
                'user_id' => Auth::id(),
            ]);
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to assign instructor. Please try again.'], 500);
            }
            return back()->with('error', 'Failed to assign instructor. Please try again.');
        }
    }

    public function destroy(Course $course)
    {
        try {
            DB::transaction(function () use ($course) {
                $course->delete(); // Cascades via model boot events
            });

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Course and all associated content deleted successfully!'
                ]);
            }

            return redirect()->route('content.management')
                ->with('success', 'Course and all associated content deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Course deletion failed', [
                'course_id' => $course->id,
                'error' => $e->getMessage(),
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to delete course. Please try again.'
                ], 500);
            }

            return back()->with('error', 'Failed to delete course. Please try again.');
        }
    }
}
