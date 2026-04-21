@extends('layouts.app')

@section('title', 'Create Task Sheet - EPAS-E')

@section('content')
<div class="container-fluid py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Create Task Sheet</h1>
                <a href="{{ route('content.management') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Content Management
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Task Sheet functionality is coming soon!
                    </div>

                    <!-- Breadcrumb -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <x-breadcrumb :items="[
                                ['label' => $informationSheet->module->course->course_name],
                                ['label' => 'Module ' . $informationSheet->module->module_number],
                                ['label' => 'Info Sheet ' . $informationSheet->sheet_number],
                                ['label' => 'New Task Sheet'],
                            ]" />
                        </div>
                    </div>

                    <div class="text-center py-5">
                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                        <h4>Task Sheets Coming Soon</h4>
                        <p class="text-muted">This feature is currently under development.</p>
                        <a href="{{ route('content.management') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-arrow-left me-2"></i>Back to Content Management
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection