<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use App\Models\User;
use App\Models\InstructorSection;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Handles class/section management for the JOMS LMS.
 *
 * Allows admins to:
 * - View all sections and students
 * - Assign instructors to multiple sections
 * - Remove instructor assignments
 *
 * Instructors can:
 * - View all sections they are assigned to
 * - Manage students in their sections
 */
class ClassController extends Controller
{
    /**
     * Display the class management index.
     *
     * @param Request $request
     * @return View
     */
    public function index(): View
    {
        return view('private.class-management.index');
    }

    /**
     * Show a specific section's details.
     *
     * @param string $section
     * @return View
     */
    public function show(string $section): View
    {
        $viewer = Auth::user();

        // INSTRUCTOR RESTRICTION: Can only view their assigned sections
        if ($viewer->role === Roles::INSTRUCTOR) {
            if (!$viewer->isAssignedToSection($section)) {
                abort(403, 'You can only view sections you are assigned to.');
            }
        }

        $instructorSections = $this->getInstructorAccessibleSections($viewer);

        $allSectionsQuery = User::where('role', Roles::STUDENT)
            ->whereNotNull('section');

        if ($viewer->role === Roles::INSTRUCTOR) {
            $allSectionsQuery->whereIn('section', $instructorSections);
        }

        $allSections = $allSectionsQuery
            ->select('section')
            ->distinct()
            ->orderBy('section')
            ->pluck('section')
            ->filter();

        $instructors = $viewer->role === Roles::ADMIN
            ? User::where('role', Roles::INSTRUCTOR)
                ->with('department')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
            : collect();

        // Get all advisers for this section (supports multiple)
        $currentAdvisers = $this->getAdvisersForSection($section);
        $advisersBySection = $this->getAdvisersBySection($allSections);

        $students = User::where('role', Roles::STUDENT)
            ->with('department')
            ->where('section', $section)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20);

        return view('private.class-management.show', [
            'section' => $section,
            'allSections' => $allSections,
            'students' => $students,
            'instructors' => $instructors,
            'currentAdviser' => $currentAdvisers->first(), // Legacy compatibility
            'currentAdvisers' => $currentAdvisers, // Multiple advisers
            'advisersBySection' => $advisersBySection,
            'isInstructor' => $viewer->role === Roles::INSTRUCTOR,
        ]);
    }

    /**
     * Assign an instructor to a section.
     * Now supports assigning the same instructor to multiple sections.
     *
     * @param Request $request
     * @param string $section
     * @return RedirectResponse
     */
    public function assignAdviser(Request $request, string $section): RedirectResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'adviser_id' => 'required|exists:users,id'
        ]);

        try {
            $adviser = User::where('role', Roles::INSTRUCTOR)
                ->where('id', $request->adviser_id)
                ->first();

            if (!$adviser) {
                return redirect()->back()->with('error', 'Instructor not found.');
            }

            // Check if already assigned to this section
            $existingAssignment = InstructorSection::where('user_id', $adviser->id)
                ->where('section', $section)
                ->first();

            if ($existingAssignment) {
                return redirect()->back()
                    ->with('info', "{$adviser->full_name} is already assigned to {$section}.");
            }

            // Create new assignment (instructor can have multiple sections now)
            InstructorSection::create([
                'user_id' => $adviser->id,
                'section' => $section,
                'is_primary' => !InstructorSection::where('user_id', $adviser->id)->exists(),
            ]);

            // Update legacy advisory_section if not set
            if (!$adviser->advisory_section) {
                $adviser->advisory_section = $section;
                $adviser->save();
            }

            return redirect()->back()
                ->with('success', "{$adviser->full_name} has been assigned to {$section}.");
        } catch (\Exception $e) {
            Log::error('Adviser assignment failed', ['error' => $e->getMessage(), 'section' => $section, 'user_id' => Auth::id()]);
            return redirect()->back()->with('error', 'Failed to assign adviser. Please try again.');
        }
    }

    /**
     * Remove an instructor from a section.
     *
     * @param Request $request
     * @param string $section
     * @return RedirectResponse
     */
    public function removeAdviser(Request $request, string $section): RedirectResponse
    {
        $this->authorizeAdmin();

        $adviserId = $request->get('adviser_id');

        try {
            if ($adviserId) {
                // Remove specific adviser from section
                $adviser = User::find($adviserId);
                if ($adviser) {
                    InstructorSection::where('user_id', $adviserId)
                        ->where('section', $section)
                        ->delete();

                    // Update legacy field if needed
                    if ($adviser->advisory_section === $section) {
                        $otherSection = InstructorSection::where('user_id', $adviserId)->first();
                        $adviser->advisory_section = $otherSection?->section;
                        $adviser->save();
                    }

                    return redirect()->back()
                        ->with('success', "{$adviser->full_name} has been removed from {$section}.");
                }
            } else {
                // Legacy: Remove all advisers from section
                $advisers = $this->getAdvisersForSection($section);

                foreach ($advisers as $adviser) {
                    InstructorSection::where('user_id', $adviser->id)
                        ->where('section', $section)
                        ->delete();

                    if ($adviser->advisory_section === $section) {
                        $otherSection = InstructorSection::where('user_id', $adviser->id)->first();
                        $adviser->advisory_section = $otherSection?->section;
                        $adviser->save();
                    }
                }

                // Also handle legacy advisory_section
                User::where('role', Roles::INSTRUCTOR)
                    ->where('advisory_section', $section)
                    ->update(['advisory_section' => null]);

                if ($advisers->isNotEmpty()) {
                    return redirect()->back()
                        ->with('success', "Advisers have been removed from {$section}.");
                }
            }

            return redirect()->back()->with('error', 'No adviser found for this section.');
        } catch (\Exception $e) {
            Log::error('Adviser removal failed', ['error' => $e->getMessage(), 'section' => $section, 'user_id' => Auth::id()]);
            return redirect()->back()->with('error', 'Failed to remove adviser. Please try again.');
        }
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Get sections accessible by an instructor.
     *
     * @param User $user
     * @return \Illuminate\Support\Collection
     */
    private function getInstructorAccessibleSections(User $user): \Illuminate\Support\Collection
    {
        if ($user->role !== Roles::INSTRUCTOR) {
            return collect();
        }

        return $user->getAllAccessibleSections();
    }

    /**
     * Get all advisers grouped by section.
     * Supports multiple advisers per section.
     *
     * @param \Illuminate\Support\Collection $sections
     * @return \Illuminate\Support\Collection
     */
    private function getAdvisersBySection($sections): \Illuminate\Support\Collection
    {
        // Get from new pivot table
        $assignments = InstructorSection::with('instructor')
            ->whereIn('section', $sections)
            ->get()
            ->groupBy('section')
            ->map(fn($items) => $items->map(fn($item) => $item->instructor)->filter());

        // Include legacy advisory_section assignments
        $legacyAdvisers = User::where('role', Roles::INSTRUCTOR)
            ->whereIn('advisory_section', $sections)
            ->get();

        foreach ($legacyAdvisers as $adviser) {
            $section = $adviser->advisory_section;
            if (!isset($assignments[$section])) {
                $assignments[$section] = collect();
            }
            if (!$assignments[$section]->contains('id', $adviser->id)) {
                $assignments[$section]->push($adviser);
            }
        }

        return $assignments;
    }

    /**
     * Get all advisers for a specific section.
     *
     * @param string $section
     * @return \Illuminate\Support\Collection
     */
    private function getAdvisersForSection(string $section): \Illuminate\Support\Collection
    {
        // Get from pivot table
        $advisers = InstructorSection::with('instructor')
            ->where('section', $section)
            ->get()
            ->map(fn($item) => $item->instructor)
            ->filter();

        // Include legacy advisory_section
        $legacyAdviser = User::where('role', Roles::INSTRUCTOR)
            ->where('advisory_section', $section)
            ->first();

        if ($legacyAdviser && !$advisers->contains('id', $legacyAdviser->id)) {
            $advisers->push($legacyAdviser);
        }

        return $advisers;
    }

    /**
     * Show section detail view with students table.
     *
     * @param string $sectionFilter
     * @param string|null $search
     * @param \Illuminate\Support\Collection $allSections
     * @param \Illuminate\Support\Collection $studentsBySection
     * @param \Illuminate\Support\Collection $instructors
     * @param \Illuminate\Support\Collection $advisersBySection
     * @param User $viewer
     * @return View
     */
    private function showSectionDetail(
        string $sectionFilter,
        ?string $search,
        $allSections,
        $studentsBySection,
        $instructors,
        $advisersBySection,
        User $viewer
    ): View {
        $students = User::where('role', Roles::STUDENT)
            ->with('department')
            ->where('section', $sectionFilter)
            ->when($search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', '%'.$search.'%')
                      ->orWhere('last_name', 'like', '%'.$search.'%')
                      ->orWhere('student_id', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20);

        $currentAdvisers = $this->getAdvisersForSection($sectionFilter);

        return view('private.class-management.index', [
            'allSections' => $allSections,
            'studentsBySection' => $studentsBySection,
            'students' => $students,
            'sectionFilter' => $sectionFilter,
            'search' => $search,
            'instructors' => $instructors,
            'currentAdviser' => $currentAdvisers->first(), // Legacy compatibility
            'currentAdvisers' => $currentAdvisers, // Multiple advisers
            'advisersBySection' => $advisersBySection,
            'isInstructor' => $viewer->role === Roles::INSTRUCTOR,
        ]);
    }
}
