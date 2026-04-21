@extends('layouts.app')

@section('title', $module->module_number . ' - ' . $module->module_name . ' - EPAS-E')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/module-unified.css') }}">
@php
$categoryColor = $course->category?->color ?? '#6d9773';
$categoryColorDark = $course->category?->color ? \App\Helpers\ColorHelper::darken($course->category->color, 15) : '#0c3a2d';
@endphp
<style>
    /* Category-specific colors */
    :root {
        --category-color: {
                {
                $categoryColor
            }
        }

        ;

        --category-color-dark: {
                {
                $categoryColorDark
            }
        }

        ;
    }

    .module-category-accent {
        color: var(--category-color) !important;
    }

    .btn-category {
        background: var(--primary);
        border-color: var(--category-color);
        color: white;
    }

    .btn-category:hover,
    .btn-category:focus {
        background: var(--primary-dark);
        border-color: var(--category-color-dark);
        color: white;
    }

    .progress-circle-fill {
        stroke: var(--category-color);
    }

    .card-header .fa-bullseye,
    .card-header .fa-book-open,
    .card-header .fa-list-ol,
    .card-header .fa-info-circle {
        color: var(--category-color) !important;
    }
</style>
@endpush

@section('content')
{{-- Module Header --}}
<div class="container-fluid py-3 bg-white border-bottom mt-4 module-header-section">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            @if($module->thumbnail)
            <img src="{{ asset('storage/' . $module->thumbnail) }}" alt="Module thumbnail"
                class="rounded shadow-sm" style="width: 120px; height: 68px; object-fit: cover;">
            @endif
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Courses', 'url' => route('courses.index')],
                    ['label' => $course->course_name, 'url' => route('courses.show', $course)],
                    ['label' => $module->module_number],
                ]" />
                <h4 class="mb-1">{{ $module->module_number }}: {{ $module->module_name }}</h4>
                <p class="text-muted mb-0 small">{{ $module->qualification_title }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-info btn-sm" id="enterFocusMode">
                <i class="fas fa-expand me-1"></i> Focus Mode
            </button>
            <a href="{{ route('courses.modules.print', [$course, $module]) }}" class="btn btn-outline-secondary btn-sm" target="_blank" title="Print Preview">
                <i class="fas fa-print me-1"></i> Print
            </a>
            <a href="{{ route('courses.modules.download', [$course, $module]) }}" class="btn btn-outline-success btn-sm" title="Download for Offline">
                <i class="fas fa-download me-1"></i> Download
            </a>
            <button class="btn btn-outline-warning btn-sm" id="saveOfflineBtn" title="Save for Offline Viewing">
                <i class="fas fa-cloud-download-alt me-1"></i> <span id="saveOfflineText">Save Offline</span>
            </button>
            <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            @if(Auth::user()->role !== 'student')
            <a href="{{ route('courses.modules.edit', [$course, $module]) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            @endif
        </div>
    </div>
</div>

<div class="container-fluid py-5">
    <div class="module-unified-layout">
        {{-- Main Content Area --}}
        <div class="main-content-section">
            {{-- Progress Card --}}
            <div class="card border-0 shadow-sm mb-4 progress-card-section">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            @php
                            $percentage = $progress ? ($progress['percentage'] ?? 0) : 0;
                            $circumference = 251.2;
                            $offset = $circumference - ($percentage / 100) * $circumference;
                            @endphp
                            <div class="position-relative progress-circle-container">
                                <svg viewBox="0 0 100 100" class="progress-circle-svg">
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#e9ecef" stroke-width="8" />
                                    <circle cx="50" cy="50" r="40" fill="none" class="progress-circle-fill" stroke-width="8"
                                        stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}" id="progressCircle" />
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle text-center">
                                    <strong id="progressText">{{ round($percentage) }}%</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row text-center">
                                <div class="col-6 col-md-3 mb-2 mb-md-0">
                                    <div class="fw-bold module-category-accent">{{ $module->informationSheets->count() }}</div>
                                    <small class="text-muted">Info Sheets</small>
                                </div>
                                <div class="col-6 col-md-3 mb-2 mb-md-0">
                                    <div class="fw-bold text-success">{{ $module->module_number }}</div>
                                    <small class="text-muted">Module #</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="fw-bold text-info">{{ $module->sector ?? 'Electronics' }}</div>
                                    <small class="text-muted">Sector</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="fw-bold text-warning" id="completedCount">{{ $progress ? ($progress['completed_items'] ?? 0) . ' of ' . ($progress['total_items'] ?? 0) : '0 of 0' }}</div>
                                    <small class="text-muted">Completed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Final Assessment Card --}}
            @if($module->require_final_assessment)
            @php
            $user = Auth::user();
            $hasPassed = $module->hasPassedAssessment($user);
            $canTake = $module->canTakeAssessment($user);
            $latestAttempt = $module->getLatestAssessmentFor($user);
            $attemptCount = $module->getAssessmentAttemptCount($user);
            $maxAttempts = $module->assessment_max_attempts;
            @endphp
            <div class="card border-0 shadow-sm mb-4 assessment-card-section">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="assessment-icon {{ $hasPassed ? 'passed' : ($canTake ? 'available' : 'locked') }}">
                                @if($hasPassed)
                                <i class="fas fa-check-circle"></i>
                                @elseif($canTake)
                                <i class="fas fa-clipboard-check"></i>
                                @else
                                <i class="fas fa-lock"></i>
                                @endif
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">Final Assessment</h6>
                                <div class="text-muted small">
                                    @if($hasPassed)
                                    <span class="text-success"><i class="fas fa-check me-1"></i>Passed with {{ number_format($latestAttempt->percentage ?? 0, 1) }}%</span>
                                    @elseif($canTake)
                                    <span>{{ $module->assessment_passing_score }}% required to pass</span>
                                    @if($module->assessment_time_limit)
                                    <span class="ms-2"><i class="fas fa-clock me-1"></i>{{ $module->assessment_time_limit }} min</span>
                                    @endif
                                    @else
                                    <span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Complete all activities first</span>
                                    @endif
                                </div>
                                @if($attemptCount > 0 && !$hasPassed)
                                <div class="text-muted small mt-1">
                                    Attempts: {{ $attemptCount }}{{ $maxAttempts ? ' / ' . $maxAttempts : '' }}
                                </div>
                                @endif
                            </div>
                        </div>
                        <div>
                            @if($hasPassed)
                            <a href="{{ route('courses.modules.assessment.results', [$course, $module, $latestAttempt]) }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-eye me-1"></i>View Results
                            </a>
                            @elseif($canTake)
                            <a href="{{ route('courses.modules.assessment.show', [$course, $module]) }}" class="btn btn-category btn-sm">
                                <i class="fas fa-play me-1"></i>{{ $attemptCount > 0 ? 'Retake' : 'Start' }} Assessment
                            </a>
                            @else
                            <a href="{{ route('courses.modules.assessment.show', [$course, $module]) }}" class="btn btn-outline-secondary btn-sm disabled">
                                <i class="fas fa-lock me-1"></i>Locked
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Content Area --}}
            <div id="contentArea">
                {{-- Overview (Default) --}}
                <div class="content-section" id="overviewSection">
                    {{-- Module Details --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fas fa-info module-category-accent me-2"></i>Module Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted d-block">Qualification Title</small>
                                    <span>{{ $module->qualification_title }}</span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted d-block">Unit of Competency</small>
                                    <span>{{ $module->unit_of_competency }}</span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted d-block">Module Number</small>
                                    <span>{{ $module->module_number }}</span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted d-block">Sector</small>
                                    <span>{{ $module->sector ?? 'Electronics' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($module->introduction)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fas fa-book-open module-category-accent me-2"></i>Introduction</h6>
                        </div>
                        <div class="card-body">
                            {!! $module->introduction !!}
                        </div>
                    </div>
                    @endif

                    @if($module->how_to_use_cblm)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fas fa-info-circle module-category-accent me-2"></i>How to Use This CBLM</h6>
                        </div>
                        <div class="card-body">
                            {!! $module->how_to_use_cblm !!}
                        </div>
                    </div>
                    @endif

                    @if($module->table_of_contents)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fas fa-list module-category-accent me-2"></i>Table of Contents</h6>
                        </div>
                        <div class="card-body">
                            {!! $module->table_of_contents !!}
                        </div>
                    </div>
                    @endif

                    @if($module->learning_outcomes)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fas fa-bullseye module-category-accent me-2"></i>Learning Outcomes</h6>
                        </div>
                        <div class="card-body">
                            {!! $module->learning_outcomes !!}
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Dynamic Content (loaded via AJAX) --}}
                <div id="dynamicContent" style="display: none;"></div>
            </div>

            {{-- Footer Navigation --}}
            @php
            $courseModules = $course->modules()->where('is_active', true)->orderBy('order')->get();
            $currentIndex = $courseModules->search(fn($m) => $m->id === $module->id);
            $prevModule = $currentIndex > 0 ? $courseModules[$currentIndex - 1] : null;
            $nextModule = $currentIndex !== false && $currentIndex < $courseModules->count() - 1 ? $courseModules[$currentIndex + 1] : null;
                @endphp
        </div>

        {{-- Right Sidebar --}}
        <div class="sidebar-section">
            <div class="toc-sidebar">
                {{-- Navigation Buttons --}}
                <div class="sidebar-nav-buttons">
                    <button class="btn btn-outline-secondary btn-sm" id="sidebarPrev" disabled>
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>
                    <button class="btn btn-category btn-sm" id="sidebarNext">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                {{-- Module Actions --}}
                <div class="sidebar-actions">
                    <a href="{{ route('courses.modules.download', [$course, $module]) }}" class="sidebar-action-btn">
                        <i class="fas fa-download"></i> Download Module PDF
                    </a>
                </div>

                {{-- Table of Contents --}}
                <div class="sidebar-toc">
                    <div class="sidebar-toc-title">
                        <i class="fas fa-list"></i> Table of Contents
                        <span class="badge bg-success ms-auto" id="progressBadge">{{ round($progress ? ($progress['percentage'] ?? 0) : 0) }}%</span>
                    </div>

                    {{-- Overview --}}
                    <div class="sidebar-toc-item">
                        <div class="sidebar-toc-link active" data-section="overview">
                            <i class="fas fa-home sidebar-toc-icon"></i>
                            Module Overview
                        </div>
                    </div>

                    {{-- Information Sheets --}}
                    @foreach($module->informationSheets as $sheetIndex => $sheet)
                    @php
                        // Sequential locking: first sheet always unlocked, others require previous sheet completed
                        $isStudent = auth()->user() && auth()->user()->role === \App\Constants\Roles::STUDENT;
                        $isLocked = false;
                        if ($isStudent && $sheetIndex > 0) {
                            $prevSheet = $module->informationSheets[$sheetIndex - 1];
                            $isLocked = !($sheetCompletion[$prevSheet->id] ?? false);
                        }
                        $isCompleted = $sheetCompletion[$sheet->id] ?? false;
                    @endphp
                    <div class="sidebar-toc-item sidebar-sheet-item {{ $isLocked ? 'sheet-locked' : '' }} {{ $isCompleted ? 'sheet-completed' : '' }}">
                        {{-- Sheet Header --}}
                        <div class="sidebar-toc-link sidebar-sheet-header {{ $isLocked ? 'locked' : '' }}" data-sheet-id="{{ $sheet->id }}" data-sheet-index="{{ $sheetIndex }}" @if($isLocked) title="Complete the previous information sheet first" @endif>
                            <i class="fas {{ $isLocked ? 'fa-lock' : ($isCompleted ? 'fa-check-circle' : 'fa-book-open') }} sidebar-toc-icon {{ $isCompleted ? 'text-success' : '' }}"></i>
                            <div class="sidebar-sheet-title">
                                <div class="sidebar-sheet-main">{{ $sheet->sheet_number }}. {{ $sheet->title }}</div>
                                <div class="sidebar-sheet-sub">
                                    {{ $sheet->topics->count() }} topics
                                    @if($sheet->selfChecks->count() > 0), {{ $sheet->selfChecks->count() }} self-check(s)@endif
                                </div>
                            </div>
                        </div>

                        {{-- Dropdown Toggle (separate small button) --}}
                        <button class="sidebar-dropdown-toggle" data-sheet-id="{{ $sheet->id }}" title="Show contents">
                            <i class="fas fa-chevron-down"></i>
                        </button>

                        {{-- Topics Dropdown (collapsed by default) --}}
                        <div class="sidebar-topics-dropdown">
                            {{-- Topics --}}
                            @if($sheet->topics && $sheet->topics->count() > 0)
                            @foreach($sheet->topics as $topicIndex => $topic)
                            <div class="sidebar-topic-item" data-topic-id="{{ $topic->id }}" data-sheet-id="{{ $sheet->id }}" data-focus-index="{{ $topicIndex }}">
                                <i class="fas fa-file-alt sidebar-topic-icon"></i>
                                <span>{{ $topic->topic_number ?? ($topicIndex + 1) }}. {{ $topic->title }}</span>
                            </div>
                            @endforeach
                            @endif

                            {{-- Self-Checks --}}
                            @if($sheet->selfChecks && $sheet->selfChecks->count() > 0)
                            @foreach($sheet->selfChecks as $scIndex => $sc)
                            <a href="{{ route('self-checks.show', $sc) }}" class="sidebar-topic-item sidebar-topic-link">
                                <i class="fas fa-clipboard-check sidebar-topic-icon" style="color: #ffc107;"></i>
                                <span>Self-Check {{ $scIndex + 1 }}: {{ Str::limit($sc->title, 25) }}</span>
                            </a>
                            @endforeach
                            @endif

                            {{-- Task Sheets --}}
                            @if($sheet->taskSheets && $sheet->taskSheets->count() > 0)
                            @foreach($sheet->taskSheets as $tsIndex => $ts)
                            <a href="{{ route('task-sheets.show', $ts) }}" class="sidebar-topic-item sidebar-topic-link">
                                <i class="fas fa-clipboard-list sidebar-topic-icon" style="color: #0dcaf0;"></i>
                                <span>Task Sheet {{ $tsIndex + 1 }}</span>
                            </a>
                            @endforeach
                            @endif

                            {{-- Job Sheets --}}
                            @if($sheet->jobSheets && $sheet->jobSheets->count() > 0)
                            @foreach($sheet->jobSheets as $jsIndex => $js)
                            <a href="{{ route('job-sheets.show', $js) }}" class="sidebar-topic-item sidebar-topic-link">
                                <i class="fas fa-hard-hat sidebar-topic-icon" style="color: #198754;"></i>
                                <span>Job Sheet {{ $jsIndex + 1 }}</span>
                            </a>
                            @endforeach
                            @endif

                            {{-- Document Assessments --}}
                            @if($sheet->documentAssessments && $sheet->documentAssessments->count() > 0)
                            @foreach($sheet->documentAssessments as $da)
                            <a href="{{ route('document-assessments.show', $da) }}" class="sidebar-topic-item sidebar-topic-link">
                                <i class="fas fa-file-word sidebar-topic-icon" style="color: #6f42c1;"></i>
                                <span>{{ $da->title }}</span>
                            </a>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Mobile TOC Toggle --}}
<button class="btn btn-success toc-mobile-toggle d-lg-none" id="tocMobileToggle">
    <i class="fas fa-list"></i>
</button>

{{-- Focus Mode Floating Button --}}
<button class="btn btn-success focus-mode-btn" id="focusModeFloatingBtn" title="Enter Focus Mode">
    <i class="fas fa-expand"></i>
</button>

{{-- Focus Mode Container --}}
<div class="focus-mode-container" id="focusModeContainer">
    <div class="focus-mode-header">
        <div class="d-flex align-items-center">
            <h5><i class="fas fa-book-reader me-2"></i><span id="focusModeTitle">{{ $module->module_name }}</span></h5>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-light text-dark" id="focusProgressBadge">1 / 1</span>
            <button class="btn btn-light btn-sm" id="exitFocusMode">
                <i class="fas fa-times me-1"></i> Exit Focus Mode
            </button>
        </div>
    </div>
    <div class="focus-mode-body">
        <div class="focus-image-panel" id="focusImagePanel">
            <div class="no-image" id="focusNoImage">
                <i class="fas fa-image"></i>
                <p>No images for this section</p>
            </div>
            <img src="" alt="" id="focusImage" style="display: none;">
            <p class="image-caption" id="focusImageCaption"></p>
            <div class="image-nav" id="imageNav" style="display: none;">
                <button id="prevImage"><i class="fas fa-chevron-left"></i></button>
                <button id="nextImage"><i class="fas fa-chevron-right"></i></button>
            </div>
            <p class="image-counter" id="imageCounter"></p>
        </div>
        <div class="focus-content-panel" id="focusContentPanel">
            <h2 id="focusContentTitle">Content Title</h2>
            <div class="content-body" id="focusContentBody">
                <p>Loading content...</p>
            </div>
        </div>
    </div>
    <div class="focus-side-nav prev" id="focusPrevBtn">
        <i class="fas fa-chevron-left"></i>
    </div>
    <div class="focus-side-nav next" id="focusNextBtn">
        <i class="fas fa-chevron-right"></i>
    </div>
</div>

{{-- Data for JS --}}
<div id="moduleData"
    data-module-id="{{ $module->id }}"
    data-module-slug="{{ $module->slug }}"
    data-course-id="{{ $course->id }}"
    data-csrf="{{ csrf_token() }}"
    data-base-url="{{ url('/courses/' . $course->id . '/module-' . $module->slug) }}"
    style="display: none;"></div>

{{-- Focus Mode Content Data --}}
<script type="application/json" id="focusModeData">
    @php
    $focusContent = [];
    $focusContent[] = [
        'type' => 'overview',
        'title' => 'Module Overview: '.$module -> module_name,
        'content' => $module -> introduction ?? $module -> learning_outcomes ?? 'Welcome to '.$module -> module_name,
        'images' => $module -> images ?? []
    ];

    foreach($module -> informationSheets as $sheet) {
        $focusContent[] = [
            'type' => 'sheet',
            'id' => $sheet -> id,
            'title' => 'Info Sheet '.$sheet -> sheet_number.
            ': '.$sheet -> title,
            'content' => $sheet -> content ?? '',
            'images' => $sheet -> parts ? collect($sheet -> parts) -> pluck('image') -> filter() -> values() -> toArray() : []
        ];

        if ($sheet -> topics) {
            foreach($sheet -> topics as $topic) {
                $topicImages = [];
                if ($topic -> parts) {
                    foreach($topic -> parts as $part) {
                        if (!empty($part['image'])) {
                            $topicImages[] = ['url' => $part['image'], 'caption' => $part['title'] ?? ''];
                        }
                    }
                }
                $focusContent[] = [
                    'type' => 'topic', 'id' => $topic -> id, 'sheetId' => $sheet -> id,
                    'title' => $topic -> title, 'content' => $topic -> content ?? '',
                    'parts' => $topic -> parts ?? [], 'images' => $topicImages
                ];
            }
        }

        // Self-Checks
        if ($sheet -> selfChecks && $sheet -> selfChecks -> count() > 0) {
            foreach($sheet -> selfChecks as $scIndex => $sc) {
                $questions = [];
                if ($sc -> questions) {
                    foreach($sc -> questions as $qIndex => $q) {
                        $questions[] = [
                            'id' => $q -> id, 'index' => $qIndex, 'type' => $q -> question_type,
                            'text' => $q -> question_text, 'points' => $q -> points ?? 1,
                            'options' => $q -> options ?? [],
                            'image' => $q -> image_path ? Storage::url($q -> image_path) : null,
                            'audio' => $q -> audio_path ? Storage::url($q -> audio_path) : null,
                            'video' => $q -> video_url ?? null,
                        ];
                    }
                }
                $focusContent[] = [
                    'type' => 'self_check', 'id' => $sc -> id, 'sheetId' => $sheet -> id,
                    'sheetTitle' => $sheet -> title, 'title' => 'Self-Check: '.$sc -> title,
                    'description' => $sc -> description ?? 'Test your understanding.',
                    'url' => route('self-checks.show', $sc),
                    'submitUrl' => route('self-checks.submit', $sc),
                    'questions' => $questions, 'questionCount' => count($questions),
                    'passingScore' => $sc -> passing_score ?? 70,
                    'revealAnswers' => $sc -> reveal_answers ?? true,
                    'randomizeQuestions' => $sc -> randomize_questions ?? false,
                    'randomizeOptions' => $sc -> randomize_options ?? false,
                    'icon' => 'clipboard-check', 'color' => '#ffc107'
                ];
            }
        }

        // Task Sheets
        if ($sheet -> taskSheets && $sheet -> taskSheets -> count() > 0) {
            foreach($sheet -> taskSheets as $tsIndex => $ts) {
                $focusContent[] = [
                    'type' => 'task_sheet', 'id' => $ts -> id, 'sheetId' => $sheet -> id,
                    'sheetTitle' => $sheet -> title,
                    'title' => 'Task Sheet: '.($ts -> title ?? 'Task Sheet '.($tsIndex + 1)),
                    'description' => $ts -> description ?? 'Complete the tasks to practice.',
                    'url' => route('task-sheets.show', $ts),
                    'icon' => 'clipboard-list', 'color' => '#0dcaf0'
                ];
            }
        }

        // Job Sheets
        if ($sheet -> jobSheets && $sheet -> jobSheets -> count() > 0) {
            foreach($sheet -> jobSheets as $jsIndex => $js) {
                $focusContent[] = [
                    'type' => 'job_sheet', 'id' => $js -> id, 'sheetId' => $sheet -> id,
                    'sheetTitle' => $sheet -> title,
                    'title' => 'Job Sheet: '.($js -> title ?? 'Job Sheet '.($jsIndex + 1)),
                    'description' => $js -> description ?? 'Apply your knowledge.',
                    'url' => route('job-sheets.show', $js),
                    'icon' => 'hard-hat', 'color' => '#198754'
                ];
            }
        }
    }
    @endphp
    @json($focusContent)
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/module-unified.js') }}"></script>
@endpush