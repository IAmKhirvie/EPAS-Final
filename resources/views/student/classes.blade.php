@extends('layouts.app')

@section('title', 'My Class - EPAS-E')

@push('styles')
<style>
.class-header {
    background: #4facfe;
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}
.class-header h2 {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
}
.class-header p {
    margin: 0;
    opacity: 0.9;
}
.class-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: rgba(255,255,255,0.2);
    border-radius: 6px;
    font-weight: 600;
    margin-right: 0.5rem;
}

.stat-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: white;
    padding: 1.25rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #e9ecef;
    text-align: center;
}
.stat-card-value {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
}
.stat-card-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
.stat-card-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    font-size: 1.25rem;
}
.stat-card-icon.blue { background: #fff8e1; color: #ffb902; }
.stat-card-icon.green { background: #e8f5e9; color: #388e3c; }
.stat-card-icon.purple { background: #f3e5f5; color: #7b1fa2; }

.section-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #e9ecef;
    margin-bottom: 1.5rem;
}
.section-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.section-title i {
    color: #ffb902;
}

.instructor-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 0.75rem;
}
.instructor-card:last-child {
    margin-bottom: 0;
}
.instructor-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.instructor-info {
    flex: 1;
}
.instructor-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.instructor-role {
    font-size: 0.85rem;
    color: #6c757d;
}

.classmate-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}
.classmate-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.2s;
}
.classmate-card:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}
.classmate-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.classmate-info {
    flex: 1;
    min-width: 0;
}
.classmate-name {
    font-weight: 600;
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.classmate-id {
    font-size: 0.75rem;
    color: #6c757d;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}
.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.no-section-card {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 10px;
    padding: 2rem;
    text-align: center;
}
.no-section-card i {
    font-size: 3rem;
    color: #ffc107;
    margin-bottom: 1rem;
}
</style>
@endpush

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'My Class'],
    ]" />

    @if($hasSection)
        {{-- Class Header --}}
        <div class="class-header">
            <h2><i class="fas fa-users me-2"></i>My Class</h2>
            <div class="mt-2">
                <span class="class-badge"><i class="fas fa-chalkboard me-1"></i> Section: {{ $section }}</span>
                @if($schoolYear)
                    <span class="class-badge"><i class="fas fa-calendar-alt me-1"></i> {{ $schoolYear }}</span>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="stat-cards">
            <div class="stat-card">
                <div class="stat-card-icon blue">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-card-value">{{ $totalStudents }}</div>
                <div class="stat-card-label">Total Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon green">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-card-value">{{ $instructors->count() }}</div>
                <div class="stat-card-label">Instructor(s)</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon purple">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-card-value">{{ $classmates->count() }}</div>
                <div class="stat-card-label">Classmates</div>
            </div>
        </div>

        <div class="row">
            {{-- Instructors --}}
            <div class="col-lg-4 mb-4">
                <div class="section-card h-100">
                    <div class="section-title">
                        <i class="fas fa-chalkboard-teacher"></i> Instructor(s)
                    </div>
                    @if($instructors->count() > 0)
                        @foreach($instructors as $instructor)
                            <div class="instructor-card">
                                <img src="{{ $instructor->profile_image_url }}" alt="{{ $instructor->full_name }}" class="instructor-avatar">
                                <div class="instructor-info">
                                    <div class="instructor-name">{{ $instructor->full_name }}</div>
                                    <div class="instructor-role">
                                        @if($instructor->department)
                                            {{ $instructor->department->name }}
                                        @else
                                            Instructor
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">No instructor assigned yet.</p>
                    @endif
                </div>
            </div>

            {{-- Classmates --}}
            <div class="col-lg-8 mb-4">
                <div class="section-card h-100">
                    <div class="section-title">
                        <i class="fas fa-user-friends"></i> Classmates ({{ $classmates->count() }})
                    </div>
                    @if($classmates->count() > 0)
                        <div class="classmate-grid">
                            @foreach($classmates as $classmate)
                                <div class="classmate-card">
                                    <img src="{{ $classmate->profile_image_url }}" alt="{{ $classmate->full_name }}" class="classmate-avatar">
                                    <div class="classmate-info">
                                        <div class="classmate-name" title="{{ $classmate->full_name }}">{{ $classmate->full_name }}</div>
                                        @if($classmate->student_id)
                                            <div class="classmate-id">{{ $classmate->student_id }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-user-friends d-block"></i>
                            <p>No other classmates in your section yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        {{-- No Section Assigned --}}
        <div class="no-section-card">
            <i class="fas fa-exclamation-triangle d-block"></i>
            <h4>No Class Assigned</h4>
            <p class="text-muted mb-0">
                You haven't been assigned to a class section yet.<br>
                Please contact your instructor or administrator to get assigned to a class.
            </p>
        </div>
    @endif
</div>
@endsection
