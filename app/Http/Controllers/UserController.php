<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use App\Models\Department;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Imports\UsersImport;
use App\Services\PendingItemsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * UserController
 *
 * Handles all user management operations including:
 * - CRUD operations for users (admin only for create/delete)
 * - Role-based user listing (students, instructors, admins)
 * - Bulk operations (activate, deactivate, delete, assign section)
 * - Section management for students
 *
 * Access Control:
 * - Admin: Full access to all operations
 * - Instructor: Can view/edit students in their advisory section only
 * - Student: No access to user management
 */
class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | User Creation
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $this->authorizeAdmin();
        $departments = Department::all();
        return view('private.users.create', compact('departments'));
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validated();

        try {
            $validated['password'] = Hash::make($validated['password']);
            $validated['stat'] = $request->has('stat') ? 1 : 0;

            // Handle custom section: if section is 'custom', use custom_section value
            if (isset($validated['section']) && $validated['section'] === 'custom') {
                $validated['section'] = $request->input('custom_section', '');
            }

            // Handle role separately (not mass-assignable for security)
            $role = $validated['role'] ?? null;
            unset($validated['role']);

            $user = User::create($validated);
            if ($role) {
                $user->role = $role;
                $user->save();
            }

            return redirect()
                ->route('private.users.index')
                ->with('success', 'User added successfully!');
        } catch (\Exception $e) {
            Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return back()->withInput()->with('error', 'Failed to create user. Please try again.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | User Listing
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $this->authorizeAdmin();
        return view('private.users.index', ['pageTitle' => 'User Management', 'roleFilter' => null]);
    }

    public function students()
    {
        $this->authorizeInstructor();
        return view('private.users.index', ['pageTitle' => 'Student Management', 'roleFilter' => Roles::STUDENT]);
    }

    public function instructors()
    {
        $this->authorizeAdmin();
        return view('private.users.index', ['pageTitle' => 'Instructor Management', 'roleFilter' => Roles::INSTRUCTOR]);
    }

    public function admins()
    {
        $this->authorizeAdmin();
        return view('private.users.index', ['pageTitle' => 'Admin Management', 'roleFilter' => Roles::ADMIN]);
    }

    /*
    |--------------------------------------------------------------------------
    | User Editing
    |--------------------------------------------------------------------------
    */

    public function edit(User $user)
    {
        $viewer = Auth::user();

        if ($viewer->role === Roles::INSTRUCTOR) {
            if ($user->role !== Roles::STUDENT) {
                abort(403, 'You can only edit student profiles.');
            }
            if (!$viewer->advisory_section || $user->section !== $viewer->advisory_section) {
                abort(403, 'You can only edit students in your advisory section.');
            }
        }

        $departments = Department::all();
        $pendingItems = app(PendingItemsService::class)->getPendingItemsForUser($user->id);

        return view('private.users.edit', compact('user', 'departments', 'pendingItems'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $viewer = Auth::user();

        if ($viewer->role === Roles::INSTRUCTOR && $user->role !== Roles::STUDENT) {
            abort(403, 'You can only edit student profiles.');
        }

        $validated = $request->validated();

        try {
            if ($validated['section'] === 'custom' && !empty($validated['custom_section'])) {
                $validated['section'] = $validated['custom_section'];
            }
            unset($validated['custom_section']);

            // Handle role separately (not mass-assignable for security)
            $role = $validated['role'] ?? null;
            unset($validated['role']);

            if ($viewer->role === Roles::INSTRUCTOR) {
                $role = null; // Instructors cannot change roles
            }

            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            if ($role && $viewer->role === Roles::ADMIN) {
                $user->role = $role;
                $user->save();
            }

            $redirectRoute = $user->role === Roles::STUDENT ? 'private.students.index' :
                            ($user->role === Roles::INSTRUCTOR ? 'private.instructors.index' : 'private.users.index');

            return redirect()->route($redirectRoute)->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            Log::error('User update failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'editor_id' => Auth::id(),
            ]);
            return back()->withInput()->with('error', 'Failed to update user. Please try again.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | User Deletion & Status Management
    |--------------------------------------------------------------------------
    */

    public function destroy(User $user)
    {
        $this->authorizeAdmin();

        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        try {
            $user->delete();
            return redirect()->route('private.users.index')->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            Log::error('User deletion failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return redirect()->back()->with('error', 'Failed to delete user. Please try again.');
        }
    }

    public function approve(User $user)
    {
        $this->authorizeAdmin();

        try {
            $user->update(['stat' => 1]);
            return redirect()->back()->with('success', 'User approved successfully!');
        } catch (\Exception $e) {
            Log::error('User approval failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return redirect()->back()->with('error', 'Failed to approve user. Please try again.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Section Management
    |--------------------------------------------------------------------------
    */

    public function removeFromClass(Request $request, User $student)
    {
        $this->authorizeInstructor();

        if ($student->role !== Roles::STUDENT) {
            abort(403, 'Can only remove students from class.');
        }

        $viewer = Auth::user();
        if ($viewer->role === Roles::INSTRUCTOR) {
            if ($viewer->advisory_section !== $student->section) {
                abort(403, 'You can only manage students in your advisory section.');
            }
        }

        try {
            $student->update(['section' => null]);
            return redirect()->back()->with('success', 'Student removed from class successfully.');
        } catch (\Exception $e) {
            Log::error('Remove from class failed', ['error' => $e->getMessage(), 'student_id' => $student->id]);
            return redirect()->back()->with('error', 'Failed to remove student from class. Please try again.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Operations
    |--------------------------------------------------------------------------
    */

    public function bulkDelete(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        try {
            $currentUserId = Auth::id();
            // Exclude current user and other admins from bulk delete for safety
            $userIds = collect($request->user_ids)
                ->filter(fn($id) => $id != $currentUserId);

            // Get users to be deleted for logging
            $usersToDelete = User::whereIn('id', $userIds)
                ->where('role', '!=', Roles::ADMIN) // Never bulk delete admins
                ->get(['id', 'email', 'role']);

            $deleted = User::whereIn('id', $usersToDelete->pluck('id'))->delete();

            // Log the bulk delete action
            Log::info('Bulk delete performed', [
                'admin_id' => $currentUserId,
                'deleted_count' => $deleted,
                'deleted_users' => $usersToDelete->toArray(),
            ]);

            return redirect()->back()->with('success', "{$deleted} user(s) deleted successfully.");
        } catch (\Exception $e) {
            Log::error('Bulk delete failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to delete users. Please try again.');
        }
    }

    public function bulkActivate(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        try {
            $updated = User::whereIn('id', $request->user_ids)->update(['stat' => 1]);
            return redirect()->back()->with('success', "{$updated} user(s) activated successfully.");
        } catch (\Exception $e) {
            Log::error('Bulk activate failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to activate users. Please try again.');
        }
    }

    public function bulkDeactivate(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        try {
            $currentUserId = Auth::id();
            $userIds = collect($request->user_ids)->filter(fn($id) => $id != $currentUserId);
            $updated = User::whereIn('id', $userIds)->update(['stat' => 0]);
            return redirect()->back()->with('success', "{$updated} user(s) deactivated successfully.");
        } catch (\Exception $e) {
            Log::error('Bulk deactivate failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to deactivate users. Please try again.');
        }
    }

    public function bulkAssignSection(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'section' => 'required|string|max:50',
        ]);

        $viewer = Auth::user();
        $query = User::whereIn('id', $request->user_ids)->where('role', Roles::STUDENT);

        if ($viewer->role === Roles::INSTRUCTOR) {
            if ($request->section !== $viewer->advisory_section) {
                return redirect()->back()->withErrors(['section' => 'You can only assign students to your advisory section.']);
            }
        }

        try {
            $updated = $query->update(['section' => $request->section]);
            return redirect()->back()->with('success', "{$updated} student(s) assigned to section {$request->section}.");
        } catch (\Exception $e) {
            Log::error('Bulk assign section failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to assign section. Please try again.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Import
    |--------------------------------------------------------------------------
    */

    /**
     * Show the bulk import form
     */
    public function showImportForm()
    {
        $this->authorizeAdmin();

        $departments = Department::all();

        return view('private.users.import', compact('departments'));
    }

    /**
     * Process the bulk import
     */
    public function processImport(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|mimetypes:text/csv,text/plain,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel|max:' . config('joms.uploads.max_document_size', 10240),
            'default_password' => 'nullable|string|min:8',
            'default_role' => 'required|in:student,instructor,admin',
            'default_department_id' => 'nullable|exists:departments,id',
            'auto_activate' => 'boolean',
        ]);

        try {
            $import = new UsersImport(
                defaultPassword: $request->input('default_password'),
                autoActivate: $request->boolean('auto_activate'),
                defaultRole: $request->input('default_role', 'student'),
                defaultDepartmentId: $request->input('default_department_id')
            );

            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $skippedRows = $import->getSkippedRows();

            // Log the import
            Log::info('Bulk user import completed', [
                'admin_id' => Auth::id(),
                'imported' => $imported,
                'skipped' => $skipped,
                'file' => $request->file('file')->getClientOriginalName(),
            ]);

            // Store skipped rows in session for display
            if (!empty($skippedRows)) {
                session()->flash('import_skipped_rows', $skippedRows);
            }

            $message = "{$imported} user(s) imported successfully.";
            if ($skipped > 0) {
                $message .= " {$skipped} row(s) skipped due to errors.";
            }

            return redirect()
                ->route('private.users.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Bulk import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => Auth::id(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Import failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Download sample import template
     */
    public function downloadTemplate()
    {
        $this->authorizeAdmin();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="user_import_template.csv"',
        ];

        $columns = [
            'first_name',
            'middle_name',
            'last_name',
            'ext_name',
            'email',
            'password',
            'role',
            'student_id',
            'section',
            'room_number',
            'department',
        ];

        $sampleData = [
            [
                'John',
                'Michael',
                'Doe',
                'Jr.',
                'john.doe@example.com',
                'Password123!',
                'student',
                'STU-2024-001',
                'Section A',
                '',
                'Information Technology',
            ],
            [
                'Jane',
                '',
                'Smith',
                '',
                'jane.smith@example.com',
                'Password123!',
                'instructor',
                '',
                '',
                'Room 101',
                'Information Technology',
            ],
        ];

        $callback = function() use ($columns, $sampleData) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Write headers
            fputcsv($file, $columns);

            // Write sample data
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}
