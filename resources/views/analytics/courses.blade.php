@extends('layouts.app')

@section('title', 'Course Analytics')

@section('content')
<div class="container-fluid py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1"><i class="fas fa-book me-2"></i>Course Analytics</h1>
                    <p class="text-muted mb-0">Course and module performance metrics</p>
                </div>
                <a href="{{ route('analytics.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Course Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 border-start border-primary border-4">
                <div class="card-body text-center">
                    <h2 class="display-6 fw-bold text-primary">{{ $metrics['total_courses'] ?? 0 }}</h2>
                    <p class="text-muted mb-0">Total Courses</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 border-start border-info border-4">
                <div class="card-body text-center">
                    <h2 class="display-6 fw-bold text-info">{{ $metrics['total_modules'] ?? 0 }}</h2>
                    <p class="text-muted mb-0">Total Modules</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 border-start border-success border-4">
                <div class="card-body text-center">
                    <h2 class="display-6 fw-bold text-success">{{ $metrics['completion_rate'] ?? 0 }}%</h2>
                    <p class="text-muted mb-0">Completion Rate</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 border-start border-warning border-4">
                <div class="card-body text-center">
                    <h2 class="display-6 fw-bold text-warning">{{ $metrics['average_progress'] ?? 0 }}%</h2>
                    <p class="text-muted mb-0">Average Progress</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Visualization -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Completion Rate</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                        <div class="text-center">
                            <div class="display-1 fw-bold text-success">{{ $metrics['completion_rate'] ?? 0 }}%</div>
                            <p class="text-muted">of students completed at least one course</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Average Progress</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                        <div class="text-center">
                            <div class="display-1 fw-bold text-primary">{{ $metrics['average_progress'] ?? 0 }}%</div>
                            <p class="text-muted">average student progress across all modules</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection