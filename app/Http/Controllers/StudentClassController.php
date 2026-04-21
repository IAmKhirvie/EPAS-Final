<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use App\Models\InstructorSection;
use App\Models\User;
use Illuminate\Http\Request;

class StudentClassController extends Controller
{
    /**
     * Display the student's class information and classmates.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Verify user is a student
        if ($user->role !== Roles::STUDENT) {
            return redirect()->route('dashboard')
                ->with('error', 'This page is only available for students.');
        }

        // Get student's section
        $section = $user->section;

        if (!$section) {
            return view('student.classes', [
                'hasSection' => false,
                'classmates' => collect(),
                'section' => null,
                'instructors' => collect(),
                'totalStudents' => 0,
            ]);
        }

        // Get classmates (students in the same section, excluding self)
        $classmates = User::where('role', Roles::STUDENT)
            ->where('section', $section)
            ->where('stat', 1) // Only active students
            ->where('id', '!=', $user->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Get instructors assigned to this section
        $instructorIds = InstructorSection::where('section', $section)->pluck('user_id');
        $instructors = User::whereIn('id', $instructorIds)
            ->orWhere(function ($query) use ($section) {
                $query->where('role', Roles::INSTRUCTOR)
                    ->where('advisory_section', $section);
            })
            ->where('stat', 1)
            ->distinct()
            ->get();

        // Total students in section (including self)
        $totalStudents = User::where('role', Roles::STUDENT)
            ->where('section', $section)
            ->where('stat', 1)
            ->count();

        return view('student.classes', [
            'hasSection' => true,
            'classmates' => $classmates,
            'section' => $section,
            'schoolYear' => $user->school_year,
            'instructors' => $instructors,
            'totalStudents' => $totalStudents,
        ]);
    }
}
