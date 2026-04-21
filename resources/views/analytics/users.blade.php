@extends('layouts.app')

@section('title', 'User Analytics')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1"><i class="fas fa-users me-2"></i>User Analytics</h1>
                    <p class="text-muted mb-0">Detailed user metrics and statistics</p>
                </div>
                <a href="{{ route('analytics.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- User Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 border-start border-primary border-4">
                <div class="card-body text-center">
                    <h2 class="display-6 fw-bold text-primary">{{ $metrics['total_students'] ?? 0 }}</h2>
                    <p class="text-muted mb-0">Total Students</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 border-start border-info border-4">
                <div class="card-body text-center">
                    <h2 class="display-6 fw-bold text-info">{{ $metrics['total_instructors'] ?? 0 }}</h2>
                    <p class="text-muted mb-0">Total Instructors</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 border-start border-success border-4">
                <div class="card-body text-center">
                    <h2 class="display-6 fw-bold text-success">{{ $metrics['active_today'] ?? 0 }}</h2>
                    <p class="text-muted mb-0">Active Today</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100 border-start border-warning border-4">
                <div class="card-body text-center">
                    <h2 class="display-6 fw-bold text-warning">{{ $metrics['pending_approval'] ?? 0 }}</h2>
                    <p class="text-muted mb-0">Pending Approval</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Stats -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>New Registrations</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="py-3 border-end">
                                <h3 class="fw-bold text-primary">{{ $metrics['new_students_today'] ?? 0 }}</h3>
                                <p class="text-muted mb-0">Today</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="py-3">
                                <h3 class="fw-bold text-success">{{ $metrics['new_students_week'] ?? 0 }}</h3>
                                <p class="text-muted mb-0">This Week</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-signal me-2"></i>Activity Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="py-3 border-end">
                                <h3 class="fw-bold text-info">{{ $metrics['active_today'] ?? 0 }}</h3>
                                <p class="text-muted mb-0">Active Today</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="py-3">
                                <h3 class="fw-bold text-secondary">{{ $metrics['active_week'] ?? 0 }}</h3>
                                <p class="text-muted mb-0">Active This Week</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
