@extends('layouts.app')

@section('title', 'Module Analytics - EPAS-E')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'My Analytics', 'url' => route('student.analytics')],
        ['label' => $module->module_number],
    ]" />

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h4 class="card-title mb-1">{{ $module->module_number }}: {{ $module->module_title }}</h4>
                    <p class="text-muted mb-0">{{ $module->module_name }}</p>
                </div>
                <div class="text-end">
                    <div class="display-5 fw-bold text-primary">{{ $moduleGrade['percentage'] }}%</div>
                    <span class="badge bg-{{ $moduleGrade['is_competent'] ? 'success' : 'warning' }}">
                        {{ $moduleGrade['grade']['descriptor'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Grade Components --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Grade Components</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold"><i class="fas fa-clipboard-check text-primary me-2"></i>Quizzes</span>
                                    <span class="fw-bold">{{ $moduleGrade['components']['self_checks']['percentage'] ?? 0 }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $moduleGrade['components']['self_checks']['percentage'] ?? 0 }}%"></div>
                                </div>
                                <small class="text-muted">{{ $moduleGrade['components']['self_checks']['count'] ?? 0 }} submissions (20% weight)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold"><i class="fas fa-book text-success me-2"></i>Homework</span>
                                    <span class="fw-bold">{{ $moduleGrade['components']['homeworks']['percentage'] ?? 0 }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $moduleGrade['components']['homeworks']['percentage'] ?? 0 }}%"></div>
                                </div>
                                <small class="text-muted">{{ $moduleGrade['components']['homeworks']['count'] ?? 0 }} submissions (30% weight)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold"><i class="fas fa-clipboard-list text-warning me-2"></i>Task Sheets</span>
                                    <span class="fw-bold">{{ $moduleGrade['components']['task_sheets']['percentage'] ?? 0 }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $moduleGrade['components']['task_sheets']['percentage'] ?? 0 }}%"></div>
                                </div>
                                <small class="text-muted">{{ $moduleGrade['components']['task_sheets']['count'] ?? 0 }} submissions (25% weight)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold"><i class="fas fa-hard-hat text-danger me-2"></i>Job Sheets</span>
                                    <span class="fw-bold">{{ $moduleGrade['components']['job_sheets']['percentage'] ?? 0 }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $moduleGrade['components']['job_sheets']['percentage'] ?? 0 }}%"></div>
                                </div>
                                <small class="text-muted">{{ $moduleGrade['components']['job_sheets']['count'] ?? 0 }} submissions (25% weight)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ranking --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Your Ranking</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-primary mb-2">#{{ $ranking['rank'] }}</div>
                    <p class="text-muted mb-2">out of {{ $ranking['total_students'] }} students</p>
                    <div class="d-flex justify-content-center align-items-center gap-2">
                        <span class="badge bg-info fs-6">Top {{ 100 - $ranking['percentile'] }}%</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Competency Status:</span>
                        <span class="fw-bold text-{{ $moduleGrade['is_competent'] ? 'success' : 'warning' }}">
                            {{ $moduleGrade['is_competent'] ? 'Competent' : 'Not Yet Competent' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Grade Code:</span>
                        <span class="fw-bold">{{ $moduleGrade['grade']['code'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Total Submissions:</span>
                        <span class="fw-bold">{{ $moduleGrade['components']['total_submissions'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('student.analytics') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Analytics
        </a>
        <a href="{{ route('courses.modules.show', [$module->course_id, $module]) }}" class="btn btn-primary">
            <i class="fas fa-book-open me-1"></i> View Module
        </a>
    </div>
</div>
@endsection
