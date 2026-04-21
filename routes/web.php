<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file contains all web routes for the application organized by feature.
| Routes are grouped logically with proper middleware, prefixes, and names.
|
| Route Groups:
| 1. Public Routes (no authentication required)
| 2. Authentication Routes (login, register, password reset)
| 3. Email Verification Routes
| 4. Protected Routes (requires authentication)
|    - Dashboard Routes
|    - User Management Routes
|    - Content Management Routes
|    - Assessment Routes
|    - Class Management Routes
|    - Announcements Routes
|    - Grades & Certificates Routes
|    - Analytics Routes
|    - Settings & Profile Routes
|    - Security Routes (2FA, Audit Logs)
|
*/

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

// Controllers
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PrivateLoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentDashboard;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleContentController;
use App\Http\Controllers\InformationSheetController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\SelfCheckController;
use App\Http\Controllers\TaskSheetController;
use App\Http\Controllers\JobSheetController;
use App\Http\Controllers\HomeworkController;
use App\Http\Controllers\PerformanceCriteriaController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DocumentAssessmentController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\EnrollmentRequestController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\GradesController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\StudentAnalyticsController;
use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\CredentialsController;
/*
|--------------------------------------------------------------------------
| Root Route & Fallback
|--------------------------------------------------------------------------
|
| The root route redirects authenticated users to their dashboard,
| and unauthenticated users to the lobby page.
|
*/
use App\Models\User;
use App\Models\Course;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/preview-certificate/{template?}', function ($template = 'tesda') {
    // Fetch a real certificate or use dummy data
    $certificate = Certificate::with(['user', 'course'])->first();

    if (!$certificate) {
        // Fallback dummy data
        $user = User::first() ?? new User(['full_name' => 'John M. Sample']);
        $course = Course::first() ?? new Course(['course_name' => 'Sample Course']);
        $issue_date = now()->format('F d, Y');
        $certificate_number = 'CERT-PREVIEW-00001';
        $config = [
            'organization' => 'EPAS-E Learning Management System',
            'institution' => config('joms.institution_name', 'IETI College of Technology - Marikina'),
            'signatory_left_title' => 'School Administrator',
            'signatory_right_title' => 'Lead Instructor / Trainer',
        ];
    } else {
        $user = $certificate->user;
        $course = $certificate->course;
        $issue_date = $certificate->issue_date?->format('F d, Y') ?? now()->format('F d, Y');
        $certificate_number = $certificate->certificate_number;
        $config = $certificate->course->certificate_config ?? [
            'organization' => 'EPAS-E Learning Management System',
            'institution' => config('joms.institution_name', 'IETI College of Technology - Marikina'),
            'signatory_left_title' => 'School Administrator',
            'signatory_right_title' => 'Lead Instructor / Trainer',
        ];
    }

    // Check if the view exists
    $viewName = "certificates.templates.{$template}";
    if (!view()->exists($viewName)) {
        $viewName = 'certificates.templates.default';
    }

    // Generate PDF instead of returning HTML view
    $pdf = Pdf::loadView($viewName, compact('user', 'course', 'issue_date', 'certificate_number', 'config'));
    $pdf->setPaper('a4', 'landscape');
    $pdf->setOptions([
        'defaultFont' => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => false,
        'margin_top' => 0,
        'margin_right' => 0,
        'margin_bottom' => 0,
        'margin_left' => 0,
    ]);

    // Stream PDF directly in browser (live preview)
    return $pdf->stream("certificate_preview_{$template}.pdf");
})->name('preview.certificate');

Route::get('/', function () {
    if (Auth::check()) {
        return app(DashboardController::class)->redirectToRoleDashboard();
    }
    return redirect()->route('welcome');
});

Route::fallback(function () {
    if (request()->expectsJson() || request()->ajax()) {
        return response()->json([
            'error' => true,
            'message' => 'The page you are looking for could not be found.',
            'status' => 404,
        ], 404);
    }

    if (Auth::check()) {
        return redirect()->route('dashboard')
            ->with('error_popup', 'The page you were looking for could not be found.')
            ->with('error_code', 404);
    }
    return redirect()->route('welcome');
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| These routes are accessible without authentication.
| Includes the welcome page, about page, and contact form.
|
*/

Route::get('/welcome', function () {
    $totalStudents = \App\Models\User::where('role', 'student')->where('stat', 1)->count();
    $totalInstructors = \App\Models\User::where('role', 'instructor')->where('stat', 1)->count();
    $totalCourses = \App\Models\Course::where('is_active', true)->count();
    $totalModules = \App\Models\Module::where('is_active', true)->count();
    return view('welcome', compact('totalStudents', 'totalInstructors', 'totalCourses', 'totalModules'));
})->name('welcome');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Public certificate verification (allows anyone to verify a certificate's authenticity)
Route::post('/verify-certificate', [CertificateController::class, 'verify'])->name('certificates.verify');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Routes for user authentication including login, registration,
| password reset, and logout functionality.
|
*/

// Student Authentication
// Note: login routes cannot use 'guest' middleware because Laravel uses route('login')
// as the redirect target for unauthenticated users, causing a redirect loop.
// LoginController::showLoginForm() handles the redirect for authenticated users instead.
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Registration (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:3,1');
});

// Password Reset (guest only - logged in users should use settings to change password)
Route::middleware('guest')->group(function () {
    Route::prefix('forgot-password')->group(function () {
        Route::get('/', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:5,1');
    });

    Route::prefix('reset-password')->group(function () {
        Route::get('/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/', [ForgotPasswordController::class, 'reset'])->name('password.update')->middleware('throttle:5,1');
    });
});

// Registration Verification (public routes for email verification flow)
Route::get('/verify-registration/{token}', [RegisterController::class, 'verifyEmail'])->name('registration.verify');
Route::post('/registration/resend', [RegisterController::class, 'resendVerification'])->middleware('throttle:3,1')->name('registration.resend');
Route::post('/registration/status', [RegisterController::class, 'checkStatus'])->middleware('throttle:5,1')->name('registration.status');

// Admin Authentication
Route::prefix('admin')->group(function () {
    Route::get('/login', [PrivateLoginController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('/login', [PrivateLoginController::class, 'adminLogin'])->name('admin.login.submit');
});

// Instructor Authentication
Route::prefix('instructor')->group(function () {
    Route::get('/login', [PrivateLoginController::class, 'showInstructorLoginForm'])->name('instructor.login');
    Route::post('/login', [PrivateLoginController::class, 'instructorLogin'])->name('instructor.login.submit');
});

// Legacy Private Login (backward compatibility - redirects to admin login)
Route::get('/private/login', function () {
    return redirect()->route('admin.login');
})->name('private.login');

/*
|--------------------------------------------------------------------------
| Email Verification Routes
|--------------------------------------------------------------------------
|
| Routes for handling email verification for registered users.
| Includes verification notice, verification link handler, and resend.
|
*/

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware(['auth'])->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = \App\Models\User::findOrFail($id);

    // Verify the hash matches the user's email
    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return redirect('/login')->withErrors(['email' => 'Invalid verification link.']);
    }

    // Check if already verified
    if ($user->hasVerifiedEmail()) {
        if (Auth::check()) {
            return redirect()->route('settings.index')->with('success', 'Email already verified.');
        }
        return redirect('/login')->with('status', 'Email already verified. You can login.');
    }

    // Mark email as verified
    $user->markEmailAsVerified();

    if (Auth::check()) {
        return redirect()->route('settings.index')->with('success', 'Email verified successfully!');
    }
    return redirect('/login')->with('status', 'Email verified successfully! You can now login.');
})->middleware(['signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $user = $request->user();

    if ($user->hasVerifiedEmail()) {
        $message = 'Your email is already verified.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return back()->with('status', $message);
    }

    try {
        $mailer = new \App\Services\PHPMailerService();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $result = $mailer->sendVerificationEmail($user, $verificationUrl);

        if ($result) {
            $message = 'Verification email sent! Please check your inbox.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return back()->with('status', $message);
        }

        $error = 'Failed to send verification email. Please try again.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $error], 500);
        }
        return back()->withErrors(['email' => $error]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Failed to send verification email: " . $e->getMessage());
        $error = 'An error occurred while sending the email. Please check your mail configuration.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $error], 500);
        }
        return back()->withErrors(['email' => $error]);
    }
})->middleware(['auth', 'throttle:3,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| Two-Factor Authentication Challenge
|--------------------------------------------------------------------------
|
| Routes for the 2FA challenge flow during login.
| Separated from main auth middleware group for the challenge process.
|
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/verify', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
});

Route::get('/private/thumbnail/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath) || !is_readable($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*')->name('private.thumbnail');
/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Authentication)
|--------------------------------------------------------------------------
|
| All routes below require the user to be authenticated.
| Routes are organized by feature area with appropriate middleware.
|
*/

Route::middleware(['auth', 'check.active', 'two-factor'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard Routes
    |--------------------------------------------------------------------------
    |
    | Role-based dashboard routing for students, instructors, and admins.
    | Each role has their own dashboard view and data endpoints.
    |
    */

    // Main dashboard router (redirects based on user role)
    Route::get('/dashboard', [DashboardController::class, 'redirectToRoleDashboard'])->name('dashboard');

    // Student Dashboard
    Route::prefix('student')->name('student.')->middleware('check.role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');
        Route::get('/dashboard-data', [DashboardController::class, 'getStudentDashboardData'])->name('dashboard-data');
        Route::get('/progress-data', [StudentDashboard::class, 'getProgressData'])->name('progress-data');
    });

    // Admin Dashboard
    Route::prefix('admin')->name('admin.')->middleware('check.role:admin,instructor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    // Instructor Dashboard (alias — redirects here from /dashboard for instructors)
    Route::prefix('instructor')->name('instructor.')->middleware('check.role:instructor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    // Global Search
    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');

    // Shared Dashboard Progress Endpoints
    Route::get('/dashboard/progress-data', [DashboardController::class, 'getProgressData']);
    Route::get('/dashboard/progress-report', [DashboardController::class, 'getProgressReport']);

    /*
    |--------------------------------------------------------------------------
    | User Management Routes
    |--------------------------------------------------------------------------
    |
    | Routes for managing users (students, instructors, admins).
    | Includes CRUD operations, bulk actions, and role-specific views.
    | Access restricted to admin and instructor roles.
    |
    */

    Route::middleware(['check.role:admin,instructor'])->group(function () {

        // User CRUD Operations
        Route::prefix('private/users')->name('private.users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/approve', [UserController::class, 'approve'])->name('approve');

            // Bulk User Actions
            Route::post('/bulk-delete', [UserController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/bulk-activate', [UserController::class, 'bulkActivate'])->name('bulk-activate');
            Route::post('/bulk-deactivate', [UserController::class, 'bulkDeactivate'])->name('bulk-deactivate');
            Route::post('/bulk-assign-section', [UserController::class, 'bulkAssignSection'])->name('bulk-assign-section');

            // Bulk Import
            Route::get('/import', [UserController::class, 'showImportForm'])->name('import');
            Route::post('/import', [UserController::class, 'processImport'])->name('import.process');
            Route::get('/import/template', [UserController::class, 'downloadTemplate'])->name('import.template');
        });

        // Role-Specific User Lists
        Route::get('/private/students', [UserController::class, 'students'])->name('private.students.index');
        Route::get('/private/instructors', [UserController::class, 'instructors'])->name('private.instructors.index');
        Route::get('/private/admins', [UserController::class, 'admins'])->name('private.admins.index');
        Route::post('/private/students/{student}/remove-from-class', [UserController::class, 'removeFromClass'])->name('private.students.remove-from-class');

        // Credential issuance from user management
        Route::post('/private/users/{user}/issue-certificate', [CertificateController::class, 'issueCertificateForUser'])->name('private.users.issue-certificate');

        // Registration Management (admin only - approve/reject pending registrations)
        Route::middleware(['check.role:admin'])->prefix('admin/registrations')->name('admin.registrations.')->group(function () {
            Route::get('/', [RegistrationController::class, 'index'])->name('index');
            Route::post('/bulk-approve', [RegistrationController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-reject', [RegistrationController::class, 'bulkReject'])->name('bulk-reject');
            Route::get('/{registration}', [RegistrationController::class, 'show'])->name('show');
            Route::post('/{registration}/approve', [RegistrationController::class, 'approve'])->name('approve');
            Route::post('/{registration}/reject', [RegistrationController::class, 'reject'])->name('reject');
            Route::post('/{registration}/resend', [RegistrationController::class, 'resendVerification'])->name('resend');
            Route::delete('/{registration}', [RegistrationController::class, 'destroy'])->name('destroy');
        });
    });

    // Instructor Index (accessible to authenticated users)
    Route::get('/instructor', [UserController::class, 'instructor'])->name('instructor.index');

    /*
    |--------------------------------------------------------------------------
    | Content Management Routes
    |--------------------------------------------------------------------------
    |
    | Routes for managing educational content including courses, modules,
    | information sheets, topics, and various assessment types.
    |
    */

    // Content Management Dashboard (Admin/Instructor only)
    Route::middleware(['check.role:admin,instructor'])->group(function () {
        Route::get('/content-management', [CourseController::class, 'contentManagement'])->name('content.management');

        // Course Management
        Route::resource('courses', CourseController::class)->except(['index', 'show']);
        Route::post('/courses/{course}/assign-instructor', [CourseController::class, 'assignInstructor'])
            ->name('courses.assign-instructor')
            ->middleware('check.role:admin');

        // Module Management (nested under courses)
        Route::prefix('courses/{course}')->name('courses.modules.')->group(function () {
            Route::get('/modules/create', [ModuleController::class, 'create'])->name('create');
            Route::post('/modules', [ModuleController::class, 'store'])->name('store');
            Route::get('/module-{module}/edit', [ModuleController::class, 'edit'])->name('edit');
            Route::put('/module-{module}', [ModuleController::class, 'update'])->name('update');
            Route::delete('/module-{module}', [ModuleController::class, 'destroy'])->name('destroy');
            Route::post('/module-{module}/upload-image', [ModuleController::class, 'uploadImage'])->name('upload-image');
            Route::delete('/module-{module}/images/{image}', [ModuleController::class, 'deleteImage'])->name('delete-image');

            // Information Sheet CRUD (nested under modules)
            Route::get('/module-{module}/sheets/create', [InformationSheetController::class, 'create'])->name('sheets.create');
            Route::post('/module-{module}/sheets', [InformationSheetController::class, 'store'])->name('sheets.store');
            Route::get('/module-{module}/sheets/{informationSheet}/edit', [InformationSheetController::class, 'edit'])->name('sheets.edit');
            Route::put('/module-{module}/sheets/{informationSheet}', [InformationSheetController::class, 'update'])->name('sheets.update');
            Route::delete('/module-{module}/information-sheets/{informationSheet}', [InformationSheetController::class, 'destroy'])->name('sheets.destroy');
            Route::get('/module-{module}/sheets/{informationSheet}/download', [InformationSheetController::class, 'download'])->name('sheets.download');
        });

        // Topic Management
        Route::prefix('information-sheets/{informationSheet}')->group(function () {
            Route::get('/topics/create', [TopicController::class, 'create'])->name('topics.create');
            Route::post('/topics', [TopicController::class, 'store'])->name('topics.store');
            Route::get('/topics/{topic}/edit', [TopicController::class, 'edit'])->name('topics.edit');
            Route::put('/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');
        });
        Route::delete('/topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');
        Route::get('/topics/{topic}/download', [TopicController::class, 'download'])->name('topics.download');
    });

    // Content View Routes (All authenticated users)
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

    // Module View Routes (nested under courses)
    Route::prefix('courses/{course}')->name('courses.modules.')->group(function () {
        // Specific routes MUST come before the catch-all {slug?} route
        Route::get('/module-{module}/progress', [ModuleController::class, 'getModuleProgress'])->name('progress');
        Route::get('/module-{module}/download', [ModuleController::class, 'downloadPdf'])->name('download');
        Route::get('/module-{module}/print', [ModuleController::class, 'printPreview'])->name('print');

        // AJAX Content Endpoints
        Route::get('/module-{module}/sheets/{informationSheet}/content', [ModuleController::class, 'getSheetContent'])->name('sheet-content');
        Route::get('/module-{module}/sheets/{informationSheet}/topics/{topic}', [ModuleController::class, 'getTopicContent'])->name('topic-content');
        Route::post('/module-{module}/sheets/{informationSheet}/topics/{topic}/complete', [ModuleController::class, 'markTopicComplete'])->name('topic-complete');
        Route::get('/module-{module}/sheets/{informationSheet}/self-check', [ModuleController::class, 'getSelfCheckContent'])->name('self-check');
        Route::get('/module-{module}/sheets/{informationSheet}/task-sheet', [ModuleController::class, 'getTaskSheetContent'])->name('task-sheet');
        Route::get('/module-{module}/sheets/{informationSheet}/job-sheet', [ModuleController::class, 'getJobSheetContent'])->name('job-sheet');

        // Module Final Assessment Routes
        Route::get('/module-{module}/final-assessment', [\App\Http\Controllers\ModuleAssessmentController::class, 'show'])->name('assessment.show');
        Route::post('/module-{module}/final-assessment', [\App\Http\Controllers\ModuleAssessmentController::class, 'submit'])->name('assessment.submit');
        Route::post('/module-{module}/final-assessment/save', [\App\Http\Controllers\ModuleAssessmentController::class, 'saveProgress'])->name('assessment.save');
        Route::get('/module-{module}/final-assessment/{submission}/results', [\App\Http\Controllers\ModuleAssessmentController::class, 'results'])->name('assessment.results');
        Route::get('/module-{module}/final-assessment/history', [\App\Http\Controllers\ModuleAssessmentController::class, 'history'])->name('assessment.history');
        Route::get('/module-{module}/final-assessment/stats', [\App\Http\Controllers\ModuleAssessmentController::class, 'stats'])->name('assessment.stats');

        // Competency Test Routes
        Route::get('/module-{module}/competency-tests', [\App\Http\Controllers\CompetencyTestController::class, 'index'])->name('competency-tests.index');
        Route::get('/module-{module}/competency-tests/create', [\App\Http\Controllers\CompetencyTestController::class, 'create'])->name('competency-tests.create');
        Route::post('/module-{module}/competency-tests', [\App\Http\Controllers\CompetencyTestController::class, 'store'])->name('competency-tests.store');
        Route::get('/module-{module}/competency-tests/{competencyTest}', [\App\Http\Controllers\CompetencyTestController::class, 'show'])->name('competency-tests.show');
        Route::get('/module-{module}/competency-tests/{competencyTest}/edit', [\App\Http\Controllers\CompetencyTestController::class, 'edit'])->name('competency-tests.edit');
        Route::put('/module-{module}/competency-tests/{competencyTest}', [\App\Http\Controllers\CompetencyTestController::class, 'update'])->name('competency-tests.update');
        Route::delete('/module-{module}/competency-tests/{competencyTest}', [\App\Http\Controllers\CompetencyTestController::class, 'destroy'])->name('competency-tests.destroy');
        Route::post('/module-{module}/competency-tests/{competencyTest}/submit', [\App\Http\Controllers\CompetencyTestController::class, 'submit'])->name('competency-tests.submit');
        Route::post('/module-{module}/competency-tests/{competencyTest}/save', [\App\Http\Controllers\CompetencyTestController::class, 'saveProgress'])->name('competency-tests.save');
        Route::get('/module-{module}/competency-tests/{competencyTest}/results/{submission}', [\App\Http\Controllers\CompetencyTestController::class, 'results'])->name('competency-tests.results');
        Route::post('/module-{module}/competency-tests/{competencyTest}/questions', [\App\Http\Controllers\CompetencyTestController::class, 'storeQuestions'])->name('competency-tests.questions.store');
        Route::get('/module-{module}/competency-tests/{competencyTest}/stats', [\App\Http\Controllers\CompetencyTestController::class, 'stats'])->name('competency-tests.stats');

        // Information Sheet View
        Route::get('/module-{module}/information-sheets/{informationSheet}', [ModuleController::class, 'showInformationSheet'])->name('information-sheet');
        Route::get('/module-{module}/information-sheets/{informationSheet}/topics/{topic}', [ModuleController::class, 'showTopic'])->name('topic');

        // Unified module show (uses slug for binding)
        Route::get('/module-{module}', [ModuleController::class, 'show'])->name('show');
    });

    // Backward Compatibility Redirects
    Route::get('/modules', function () {
        return redirect()->route('courses.index');
    })->name('modules.index');

    Route::get('/modules/{module}', function (\App\Models\Module $module) {
        return redirect()->route('courses.modules.show', [$module->course_id, $module, $module->slug]);
    })->name('modules.show');

    Route::get('/modules/{module}/download', function (\App\Models\Module $module) {
        return redirect()->route('courses.modules.download', [$module->course_id, $module]);
    })->name('modules.download');

    Route::get('/modules/{module}/print', function (\App\Models\Module $module) {
        return redirect()->route('courses.modules.print', [$module->course_id, $module]);
    })->name('modules.print');

    Route::get('/modules/{module}/progress', function (\App\Models\Module $module) {
        return redirect()->route('courses.modules.progress', [$module->course_id, $module]);
    })->name('modules.progress');

    // Backward compatibility: legacy module CRUD routes (redirect to course-nested equivalents)
    Route::get('/modules/create', function (Request $request) {
        $courseId = $request->query('course_id');
        if ($courseId) {
            return redirect()->route('courses.modules.create', $courseId);
        }
        return redirect()->route('courses.index');
    })->name('modules.create');

    Route::post('/modules', function (Request $request) {
        $courseId = $request->input('course_id');
        if ($courseId) {
            return app(ModuleController::class)->store($request, \App\Models\Course::findOrFail($courseId));
        }
        return redirect()->route('courses.index')->with('error', 'Course is required to create a module.');
    })->name('modules.store');

    Route::get('/modules/{module}/edit', function (\App\Models\Module $module) {
        return redirect()->route('courses.modules.edit', [$module->course_id, $module]);
    })->name('modules.edit');

    Route::put('/modules/{module}', function (Request $request, \App\Models\Module $module) {
        return redirect()->route('courses.modules.update', [$module->course_id, $module]);
    })->name('modules.update');

    Route::delete('/modules/{module}', function (\App\Models\Module $module) {
        return app(ModuleController::class)->destroy($module->course, $module);
    })->name('modules.destroy');

    // Backward compatibility: legacy information sheet routes
    Route::get('/modules/{module}/information-sheets/create', function (\App\Models\Module $module) {
        return redirect()->route('courses.modules.sheets.create', [$module->course_id, $module]);
    })->name('information-sheets.create');

    Route::post('/modules/{module}/information-sheets', function (Request $request, \App\Models\Module $module) {
        return app(InformationSheetController::class)->store($request, $module);
    })->name('information-sheets.store');

    Route::get('/modules/{module}/information-sheets/{informationSheet}/edit', function (\App\Models\Module $module, \App\Models\InformationSheet $informationSheet) {
        return redirect()->route('courses.modules.sheets.edit', [$module->course_id, $module, $informationSheet]);
    })->name('information-sheets.edit');

    Route::put('/modules/{module}/information-sheets/{informationSheet}', function (Request $request, \App\Models\Module $module, \App\Models\InformationSheet $informationSheet) {
        return app(InformationSheetController::class)->update($request, $informationSheet);
    })->name('information-sheets.update');

    Route::delete('/modules/{module}/information-sheets/{informationSheet}', function (\App\Models\Module $module, \App\Models\InformationSheet $informationSheet) {
        return app(InformationSheetController::class)->destroy($informationSheet);
    })->name('information-sheets.destroy');

    Route::get('/modules/{module}/information-sheets/{informationSheet}/download', function (\App\Models\Module $module, \App\Models\InformationSheet $informationSheet) {
        return app(InformationSheetController::class)->download($informationSheet);
    })->name('information-sheets.download');

    Route::get('/modules/{module}/information-sheets/{informationSheet}', function (\App\Models\Module $module, \App\Models\InformationSheet $informationSheet) {
        return redirect()->route('courses.modules.show', [$module->course_id, $module, $module->slug]);
    })->name('information-sheets.show');

    // Backward compatibility: legacy AJAX content routes
    Route::get('/modules/information-sheets/{informationSheet}/content', function (\App\Models\InformationSheet $informationSheet) {
        $module = $informationSheet->module;
        return redirect()->route('courses.modules.sheet-content', [$module->course_id, $module, $informationSheet]);
    })->name('information-sheets.content');

    Route::get('/modules/information-sheets/{informationSheet}/topics/{topic}', function (\App\Models\InformationSheet $informationSheet, \App\Models\Topic $topic) {
        $module = $informationSheet->module;
        return redirect()->route('courses.modules.topic-content', [$module->course_id, $module, $informationSheet, $topic]);
    })->name('information-sheets.topics.content');

    // Backward compatibility: legacy self-check by sheet route
    Route::get('/modules/{module}/information-sheets/{informationSheet}/self-check', function (\App\Models\Module $module, \App\Models\InformationSheet $informationSheet) {
        return redirect()->route('courses.modules.information-sheets.self-check', [$module->course_id, $module, $informationSheet]);
    })->name('modules.information-sheets.self-check');

    // Topic Content Routes (keep flat for AJAX compatibility)
    Route::get('/topics/{topic}/content', [TopicController::class, 'getContent'])->name('topics.content');

    // Module Content API
    Route::get('/module-content/{module}/{contentType}', [ModuleContentController::class, 'show'])->name('module-content.show');
    Route::get('/api/module-content/{module}/{contentType}', [ModuleContentController::class, 'getContentApi'])->name('api.module-content.show');

    /*
    |--------------------------------------------------------------------------
    | Assessment Routes
    |--------------------------------------------------------------------------
    |
    | Routes for managing and taking assessments including self-checks,
    | task sheets, job sheets, homework, and competency checklists.
    |
    */

    // Assessment Management (Admin/Instructor only)
    Route::middleware(['check.role:admin,instructor'])->group(function () {

        // Self Check Management
        Route::prefix('information-sheets/{informationSheet}')->group(function () {
            Route::get('/self-checks/create', [SelfCheckController::class, 'create'])->name('self-checks.create');
            Route::post('/self-checks', [SelfCheckController::class, 'store'])->name('self-checks.store');
            Route::get('/self-checks/{selfCheck}/edit', [SelfCheckController::class, 'edit'])->name('self-checks.edit');
            Route::put('/self-checks/{selfCheck}', [SelfCheckController::class, 'update'])->name('self-checks.update');
            Route::delete('/self-checks/{selfCheck}', [SelfCheckController::class, 'destroy'])->name('self-checks.destroy');
        });

        // Quiz Media Upload
        Route::post('/quiz/upload-image', [SelfCheckController::class, 'uploadImage'])->name('quiz.upload-image');
        Route::post('/quiz/upload-audio', [SelfCheckController::class, 'uploadAudio'])->name('quiz.upload-audio');
        Route::post('/quiz/upload-video', [SelfCheckController::class, 'uploadVideo'])->name('quiz.upload-video');

        // Task Sheet Management
        Route::prefix('information-sheets/{informationSheet}')->group(function () {
            Route::get('/task-sheets/create', [TaskSheetController::class, 'create'])->name('task-sheets.create');
            Route::post('/task-sheets', [TaskSheetController::class, 'store'])->name('task-sheets.store');
            Route::get('/task-sheets/{taskSheet}/edit', [TaskSheetController::class, 'edit'])->name('task-sheets.edit');
            Route::put('/task-sheets/{taskSheet}', [TaskSheetController::class, 'update'])->name('task-sheets.update');
            Route::delete('/task-sheets/{taskSheet}', [TaskSheetController::class, 'destroy'])->name('task-sheets.destroy');
        });

        // Job Sheet Management
        Route::prefix('information-sheets/{informationSheet}')->group(function () {
            Route::get('/job-sheets/create', [JobSheetController::class, 'create'])->name('job-sheets.create');
            Route::post('/job-sheets', [JobSheetController::class, 'store'])->name('job-sheets.store');
            Route::get('/job-sheets/{jobSheet}/edit', [JobSheetController::class, 'edit'])->name('job-sheets.edit');
            Route::put('/job-sheets/{jobSheet}', [JobSheetController::class, 'update'])->name('job-sheets.update');
            Route::delete('/job-sheets/{jobSheet}', [JobSheetController::class, 'destroy'])->name('job-sheets.destroy');
        });

        // Homework Management
        Route::prefix('information-sheets/{informationSheet}')->group(function () {
            Route::get('/homeworks/create', [HomeworkController::class, 'create'])->name('homeworks.create');
            Route::post('/homeworks', [HomeworkController::class, 'store'])->name('homeworks.store');
            Route::get('/homeworks/{homework}/edit', [HomeworkController::class, 'edit'])->name('homeworks.edit');
            Route::put('/homeworks/{homework}', [HomeworkController::class, 'update'])->name('homeworks.update');
            Route::delete('/homeworks/{homework}', [HomeworkController::class, 'destroy'])->name('homeworks.destroy');
        });

        // Performance Criteria Management
        Route::prefix('performance-criteria')->name('performance-criteria.')->group(function () {
            Route::get('/create', [PerformanceCriteriaController::class, 'create'])->name('create');
            Route::post('/', [PerformanceCriteriaController::class, 'store'])->name('store');
            Route::get('/{performanceCriteria}/edit', [PerformanceCriteriaController::class, 'edit'])->name('edit');
            Route::put('/{performanceCriteria}', [PerformanceCriteriaController::class, 'update'])->name('update');
            Route::delete('/{performanceCriteria}', [PerformanceCriteriaController::class, 'destroy'])->name('destroy');
        });

        // Checklist Management
        Route::prefix('information-sheets/{informationSheet}')->group(function () {
            Route::get('/checklists/create', [ChecklistController::class, 'create'])->name('checklists.create');
            Route::post('/checklists', [ChecklistController::class, 'store'])->name('checklists.store');
            Route::get('/checklists/{checklist}/edit', [ChecklistController::class, 'edit'])->name('checklists.edit');
            Route::put('/checklists/{checklist}', [ChecklistController::class, 'update'])->name('checklists.update');
            Route::delete('/checklists/{checklist}', [ChecklistController::class, 'destroy'])->name('checklists.destroy');
        });

        // Document Assessment Management
        Route::prefix('information-sheets/{informationSheet}')->group(function () {
            Route::get('/document-assessments/create', [DocumentAssessmentController::class, 'create'])->name('document-assessments.create');
            Route::post('/document-assessments', [DocumentAssessmentController::class, 'store'])->name('document-assessments.store');
            Route::get('/document-assessments/{documentAssessment}/edit', [DocumentAssessmentController::class, 'edit'])->name('document-assessments.edit');
            Route::put('/document-assessments/{documentAssessment}', [DocumentAssessmentController::class, 'update'])->name('document-assessments.update');
            Route::delete('/document-assessments/{documentAssessment}', [DocumentAssessmentController::class, 'destroy'])->name('document-assessments.destroy');
        });
        Route::post('/document-assessments/convert', [DocumentAssessmentController::class, 'convert'])->name('document-assessments.convert');
        Route::post('/document-assessment-submissions/{submission}/grade', [DocumentAssessmentController::class, 'grade'])->name('document-assessment-submissions.grade');
    });

    // Assessment View & Submit Routes (All authenticated users)

    // Document Assessments
    Route::get('/document-assessments/{documentAssessment}', [DocumentAssessmentController::class, 'show'])->name('document-assessments.show');
    Route::post('/document-assessments/{documentAssessment}/submit', [DocumentAssessmentController::class, 'submit'])->name('document-assessments.submit');
    Route::get('/document-assessments/{documentAssessment}/download', [DocumentAssessmentController::class, 'download'])->name('document-assessments.download');

    // Self Checks
    Route::get('/self-checks/{selfCheck}', [SelfCheckController::class, 'show'])->name('self-checks.show');
    Route::get('/self-checks/{selfCheck}/download', [SelfCheckController::class, 'download'])->name('self-checks.download');
    Route::post('/self-checks/{selfCheck}/submit', [SelfCheckController::class, 'submit'])->name('self-checks.submit');
    Route::get('/self-checks/{selfCheck}/results', [SelfCheckController::class, 'results'])->name('self-checks.results');
    Route::get('/courses/{course}/module-{module}/information-sheets/{informationSheet}/self-check', [SelfCheckController::class, 'showBySheet'])->name('courses.modules.information-sheets.self-check');

    // Task Sheets
    Route::get('/task-sheets/{taskSheet}', [TaskSheetController::class, 'show'])->name('task-sheets.show');
    Route::post('/task-sheets/{taskSheet}/submit', [TaskSheetController::class, 'submit'])->name('task-sheets.submit');
    Route::get('/task-sheets/{taskSheet}/download', [TaskSheetController::class, 'download'])->name('task-sheets.download');

    // Job Sheets
    Route::get('/job-sheets/{jobSheet}', [JobSheetController::class, 'show'])->name('job-sheets.show');
    Route::post('/job-sheets/{jobSheet}/submit', [JobSheetController::class, 'submit'])->name('job-sheets.submit');
    Route::get('/job-sheets/{jobSheet}/download', [JobSheetController::class, 'download'])->name('job-sheets.download');

    // Homework
    Route::get('/homeworks/{homework}', [HomeworkController::class, 'show'])->name('homeworks.show');
    Route::post('/homeworks/{homework}/submit', [HomeworkController::class, 'submit'])->name('homeworks.submit');

    // Checklists
    Route::get('/checklists/{checklist}', [ChecklistController::class, 'show'])->name('checklists.show');
    Route::post('/checklists/{checklist}/evaluate', [ChecklistController::class, 'evaluate'])->name('checklists.evaluate');

    /*
    |--------------------------------------------------------------------------
    | Class Management Routes
    |--------------------------------------------------------------------------
    |
    | Routes for managing classes/sections and enrollment requests.
    | Admin and instructors can manage class assignments and enrollments.
    |
    */

    Route::middleware(['check.role:admin,instructor'])->group(function () {

        // Class/Section Management
        Route::prefix('class-management')->name('class-management.')->group(function () {
            Route::get('/', [ClassController::class, 'index'])->name('index');
            Route::get('/{section}', [ClassController::class, 'show'])->name('show');
            Route::post('/{section}/assign-adviser', [ClassController::class, 'assignAdviser'])->name('assign-adviser');
            Route::delete('/{section}/remove-adviser', [ClassController::class, 'removeAdviser'])->name('remove-adviser');
        });

        // Enrollment Requests Management
        Route::prefix('enrollment-requests')->name('enrollment-requests.')->group(function () {
            Route::get('/', [EnrollmentRequestController::class, 'index'])->name('index');
            Route::get('/create', [EnrollmentRequestController::class, 'create'])->name('create');
            Route::post('/', [EnrollmentRequestController::class, 'store'])->name('store');
            Route::post('/{enrollmentRequest}/approve', [EnrollmentRequestController::class, 'approve'])->name('approve');
            Route::post('/{enrollmentRequest}/reject', [EnrollmentRequestController::class, 'reject'])->name('reject');
            Route::delete('/{enrollmentRequest}', [EnrollmentRequestController::class, 'cancel'])->name('cancel');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Announcement Routes
    |--------------------------------------------------------------------------
    |
    | Routes for creating and viewing announcements.
    | Admin/Instructors can create; all users can view and interact.
    |
    */

    // Announcement Management (Admin/Instructor only)
    Route::middleware(['check.role:admin,instructor'])->group(function () {
        Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('private.announcements.create');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('private.announcements.store');
        Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('private.announcements.edit');
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('private.announcements.update');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('private.announcements.destroy');
    });

    // Announcement View & Interaction (All authenticated users)
    Route::prefix('announcements')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('private.announcements.index');
        Route::get('/{announcement}', [AnnouncementController::class, 'show'])->name('private.announcements.show');
        Route::post('/{announcement}/comment', [AnnouncementController::class, 'addComment'])->name('private.announcements.comment');
    });

    // Announcement API Endpoints
    Route::prefix('api/announcements')->name('api.announcements.')->group(function () {
        Route::get('/recent', [AnnouncementController::class, 'getRecentAnnouncements'])->name('recent');
        Route::get('/unread-count', [AnnouncementController::class, 'unreadCount'])->name('unread-count');
    });

    /*
    |--------------------------------------------------------------------------
    | Grades & Certificates Routes
    |--------------------------------------------------------------------------
    |
    | Routes for viewing grades and managing/downloading certificates.
    | Students can view their own; admin/instructors can view all and export.
    |
    */

    // Grades
    Route::prefix('grades')->name('grades.')->group(function () {
        Route::get('/', [GradesController::class, 'index'])->name('index');
        Route::get('/api/my-grades', [GradesController::class, 'getStudentGradesApi'])->name('api.my-grades');
        Route::get('/export', [GradesController::class, 'exportGrades'])->name('export')->middleware('check.role:admin,instructor');
        Route::get('/export-mine', [GradesController::class, 'exportMyGrades'])->name('export-mine')->middleware('check.role:student');
        Route::get('/export-class/{section}', [GradesController::class, 'exportClassGrades'])->name('export-class')->middleware('check.role:admin,instructor');
        Route::get('/{student}', [GradesController::class, 'show'])->name('show')->where('student', '[0-9]+');
    });

    // Student Credentials (unified badges + certificates view)
    Route::get('/credentials', [CredentialsController::class, 'index'])->name('credentials.index');

    // Certificates
    Route::prefix('certificates')->name('certificates.')->group(function () {
        Route::get('/', [CertificateController::class, 'index'])->name('index');
        Route::get('/{certificate}', [CertificateController::class, 'show'])->name('show');
        Route::get('/{certificate}/download', [CertificateController::class, 'download'])->name('download');
        Route::post('/course/{course}/generate', [CertificateController::class, 'generate'])->name('generate');
    });

    // Admin Certificate Management
    Route::prefix('admin/certificates')->name('admin.certificates.')->middleware('check.role:admin,instructor')->group(function () {
        Route::get('/', [CertificateController::class, 'adminIndex'])->name('index');
        Route::get('/pending', [CertificateController::class, 'pending'])->name('pending');
        Route::get('/manual-release', [CertificateController::class, 'manualReleaseForm'])->name('manual-release');
        Route::post('/manual-release', [CertificateController::class, 'manualRelease'])->name('manual-release.store');
        Route::post('/bulk-release', [CertificateController::class, 'bulkRelease'])->name('bulk-release');
        Route::post('/distribute', [CertificateController::class, 'distributeCertificates'])->name('distribute');

        // Certificate CRUD
        Route::get('/{certificate}', [CertificateController::class, 'adminShow'])->name('show');
        Route::get('/{certificate}/edit', [CertificateController::class, 'edit'])->name('edit');
        Route::put('/{certificate}', [CertificateController::class, 'update'])->name('update');
        Route::delete('/{certificate}', [CertificateController::class, 'destroy'])->name('destroy');

        // Certificate actions
        Route::post('/{certificate}/resend-email', [CertificateController::class, 'resendEmail'])->name('resend-email');
        Route::post('/{certificate}/regenerate-pdf', [CertificateController::class, 'regeneratePdf'])->name('regenerate-pdf');
        Route::post('/{certificate}/instructor-approve', [CertificateController::class, 'instructorApprove'])->name('instructor-approve');
        Route::post('/{certificate}/admin-approve', [CertificateController::class, 'adminApprove'])->name('admin-approve');
        Route::post('/{certificate}/reject', [CertificateController::class, 'reject'])->name('reject');
        Route::post('/{certificate}/revoke', [CertificateController::class, 'revoke'])->name('revoke');
    });

    /*
    |--------------------------------------------------------------------------
    | Analytics Routes
    |--------------------------------------------------------------------------
    |
    | Routes for viewing analytics and reports.
    | Access restricted to admin and instructor roles.
    |
    */

    Route::prefix('analytics')->name('analytics.')->middleware('check.role:admin,instructor')->group(function () {
        Route::get('/', [AnalyticsController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AnalyticsController::class, 'users'])->name('users');
        Route::get('/courses', [AnalyticsController::class, 'courses'])->name('courses');

        // Analytics API Endpoints
        Route::get('/api/metrics', [AnalyticsController::class, 'getMetricsApi'])->name('api.metrics');
        Route::get('/api/top-performers', [AnalyticsController::class, 'topPerformers'])->name('api.top-performers');
        Route::get('/api/at-risk', [AnalyticsController::class, 'atRiskStudents'])->name('api.at-risk');

        // Analytics Export
        Route::get('/export/students', [AnalyticsController::class, 'exportStudentProgress'])->name('export.students');
        Route::get('/export/pdf', [AnalyticsController::class, 'exportPdfReport'])->name('export.pdf');

        // Cache Management
        Route::post('/refresh-cache', [AnalyticsController::class, 'refreshCache'])->name('refresh-cache');
    });

    /*
    |--------------------------------------------------------------------------
    | Student Analytics Routes
    |--------------------------------------------------------------------------
    |
    | Personal analytics dashboard for students to view their performance.
    | Access restricted to student role.
    |
    */

    Route::prefix('student')->name('student.')->middleware('check.role:student')->group(function () {
        Route::get('/analytics', [StudentAnalyticsController::class, 'index'])->name('analytics');
        Route::get('/analytics/module/{module}', [StudentAnalyticsController::class, 'moduleDetail'])->name('analytics.module');
        Route::get('/classes', [StudentClassController::class, 'index'])->name('classes');
    });

    /*
    |--------------------------------------------------------------------------
    | Audit Log Routes
    |--------------------------------------------------------------------------
    |
    | Routes for viewing system audit logs.
    | Access restricted to admin role only.
    |
    */

    Route::prefix('audit-logs')->name('audit-logs.')->middleware('check.role:admin')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/security', [AuditLogController::class, 'security'])->name('security');
        Route::get('/export', [AuditLogController::class, 'export'])->name('export');
        Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication Management Routes
    |--------------------------------------------------------------------------
    |
    | Routes for setting up and managing two-factor authentication.
    |
    */

    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::get('/setup', [TwoFactorController::class, 'setup'])->name('setup');
        Route::post('/enable', [TwoFactorController::class, 'enable'])->name('enable');
        Route::get('/manage', [TwoFactorController::class, 'manage'])->name('manage');
        Route::delete('/disable', [TwoFactorController::class, 'disable'])->name('disable');
        Route::post('/backup-codes', [TwoFactorController::class, 'regenerateBackupCodes'])->name('backup-codes');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile & Settings Routes
    |--------------------------------------------------------------------------
    |
    | Routes for managing user profile and application settings.
    |
    */

    // Profile
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

    // Profile Image Serving (serves uploaded profile images)
    Route::get('/storage/profile-images/{filename}', function ($filename) {
        $filename = basename($filename);
        $path = storage_path('app/public/profile-images/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    })->where('filename', '[a-zA-Z0-9_\-\.]+')->name('profile.image');

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/profile', [SettingsController::class, 'updateProfile'])->name('profile');
        Route::post('/profile-picture', [SettingsController::class, 'updateProfilePicture'])->name('profile-picture');
        Route::post('/password', [SettingsController::class, 'updatePassword'])->name('password');
        Route::post('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications');
        Route::post('/appearance', [SettingsController::class, 'updateAppearance'])->name('appearance');
        Route::post('/system', [SettingsController::class, 'updateSystem'])->name('system')->middleware('check.role:admin');
        Route::get('/export', [SettingsController::class, 'exportData'])->name('export');
        Route::post('/delete-account', [SettingsController::class, 'deleteAccount'])->name('delete-account');
        Route::post('/resend-verification', [SettingsController::class, 'resendVerification'])->name('resend-verification');
    });

    // Enrollment Requests API
    Route::get('/api/enrollment-requests/pending-count', [EnrollmentRequestController::class, 'getPendingCount'])->name('api.enrollment-requests.pending-count');

    // Real-time badge counts API
    Route::get('/api/badge-counts', function () {
        $user = auth()->user();
        $data = ['trash' => 0, 'registrations' => 0, 'enrollments' => 0];

        if ($user->role === \App\Constants\Roles::ADMIN) {
            $data['registrations'] = \App\Models\Registration::where('status', 'pending')->count();
        }

        if (in_array($user->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR])) {
            $data['enrollments'] = \App\Models\EnrollmentRequest::where('status', 'pending')->count();
        }

        return response()->json($data);
    })->name('api.badge-counts');

    /*
    |--------------------------------------------------------------------------
    | Help & Support Routes
    |--------------------------------------------------------------------------
    |
    | Routes for help and support pages.
    |
    */

    Route::get('/help-support', function () {
        return view('help-support');
    })->name('help-support');

    /*
    |--------------------------------------------------------------------------
    | Trash Routes
    |--------------------------------------------------------------------------
    |
    | Routes for viewing and managing deleted content (soft deletes).
    | Access restricted to admin and instructor roles.
    |
    */

    Route::get('/trash', [TrashController::class, 'index'])
        ->name('trash.index')
        ->middleware('check.role:admin');
});
