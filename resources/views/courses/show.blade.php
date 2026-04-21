@extends('layouts.app')

@section('title', $course->course_name . ' - EPAS-E')

@push('styles')
<style>
    /* Course Show Page */
    .course-hero {
        position: relative;
        padding: 1.25rem;
        border-radius: var(--border-radius);
        overflow: hidden;
        margin-bottom: 1rem;
        min-height: 140px;
        display: flex;
        align-items: flex-end;
    }

    .course-hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--category-color, #6d9773);
        z-index: 0;
    }

    .course-hero-bg.has-thumbnail {
        background-size: cover;
        background-position: center;
    }

    .course-hero-bg::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.2));
    }

    .course-hero-bg::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        z-index: 1;
    }

    .course-hero-content {
        position: relative;
        z-index: 2;
        color: white;
        width: 100%;
    }

    .course-hero-badges {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .course-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 0.4rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .course-hero-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .course-hero-code {
        font-size: 1rem;
        opacity: 0.9;
        margin-bottom: 0.75rem;
    }

    .course-hero-meta {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .course-hero-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .course-hero-actions {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        z-index: 3;
        display: flex;
        gap: 0.5rem;
    }

    .course-hero-actions .btn {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
    }

    .course-hero-actions .btn:hover {
        background: var(--accent-dark);
        color: var(--light);
    }

    /* Course Info Cards */
    .course-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .course-info-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--border-radius);
        padding: 1rem;
    }

    .course-info-card-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .course-info-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        background: color-mix(in srgb, var(--category-color) 15%, transparent);
        color: var(--category-color);
    }

    .course-info-card-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .course-info-card-body {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-secondary);
    }

    .info-row i {
        width: 18px;
        color: var(--category-color);
        font-size: 0.85rem;
    }

    .info-row strong {
        color: var(--text-primary);
        margin-right: 0.25rem;
    }

    /* Instructor Card */
    .instructor-profile {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .instructor-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--category-color), var(--category-color-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
        overflow: hidden;
    }

    .instructor-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .instructor-info h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.25rem 0;
    }

    .instructor-info p {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin: 0;
    }

    /* Modules Section */
    .modules-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--border-radius);
        overflow: hidden;
    }

    .modules-section-header {
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modules-section-header h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modules-section-header h3 i {
        color: var(--category-color);
    }

    .module-count-badge {
        background: var(--category-color);
        color: white;
        padding: 0.25rem 0.6rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        padding: 1.5rem;
    }

    .module-card {
        background: var(--background);
        border: 1px solid var(--border);
        border-radius: var(--border-radius);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .module-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .module-card-header {
        background: linear-gradient(135deg, var(--category-color, #64748b), var(--category-color-dark, #475569));
        padding: 0.75rem 1rem;
        color: white;
    }

    .module-card-number {
        font-size: 0.75rem;
        font-weight: 600;
        opacity: 0.9;
        margin-bottom: 0.25rem;
    }

    .module-card-name {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
    }

    .module-card-body {
        padding: 0.85rem 1rem;
    }

    .module-card-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
    }

    .module-card-meta {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .module-card-stat {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: var(--surface);
        border-radius: calc(var(--border-radius) / 2);
    }

    .module-card-stat i {
        color: var(--category-color);
    }

    .module-card-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .module-card-actions {
        display: flex;
        gap: 0.5rem;
    }

    .module-card-actions .btn {
        padding: 0.4rem 0.75rem;
        font-size: 0.8rem;
    }

    /* Empty State */
    .modules-empty {
        text-align: center;
        padding: 4rem 2rem;
    }

    .modules-empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        background: var(--background);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modules-empty-icon i {
        font-size: 2rem;
        color: var(--category-color);
    }

    .modules-empty h4 {
        font-size: 1.1rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .modules-empty p {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .course-hero {
            padding: 1.5rem;
        }

        .course-hero-title {
            font-size: 1.5rem;
        }

        .course-hero-actions {
            position: static;
            margin-top: 1rem;
        }

        .course-hero-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .modules-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Dark mode */
    .dark-mode .course-info-card {
        background: var(--surface);
        border-color: var(--border);
    }

    .dark-mode .module-card {
        background: var(--surface);
        border-color: var(--border);
    }

    .dark-mode .module-card-stat {
        background: rgba(255, 255, 255, 0.05);
    }

    .dark-mode .modules-section {
        background: var(--surface);
        border-color: var(--border);
    }

    /* Category-colored buttons */
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
</style>
@endpush

@section('content')
@php
$categoryColor = $course->category?->color ?? '#6d9773';
$categoryColorDark = $course->category?->darker_color ?? '#0c3a2d';
@endphp

<div class="container-fluid py-2" style="--category-color: {{ $categoryColor }}; --category-color-dark: {{ $categoryColorDark }}">
    <x-back-button :route="route('courses.index')" label="Back to Courses" />

    {{-- Course Hero --}}
    <div class="course-hero">
        <div class="course-hero-bg {{ $course->thumbnail ? 'has-thumbnail' : '' }}"
            style="{{ $course->thumbnail ? 'background-image: url(' . asset('storage/' . $course->thumbnail) . ');' : '' }}"></div>

        @if(isset($canEdit) && $canEdit)
        <div class="course-hero-actions">
            <a href="{{ route('courses.modules.create', $course) }}" class="btn btn-sm">
                <i class="fas fa-plus me-1"></i>Add Module
            </a>
            <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
        </div>
        @endif

        <div class="course-hero-content">
            <div class="course-hero-badges">
                <span class="course-hero-badge">
                    <i class="fas fa-book"></i>
                    {{ $course->modules->count() }} {{ Str::plural('Module', $course->modules->count()) }}
                </span>
                @if($course->category)
                <span class="course-hero-badge">
                    <i class="{{ $course->category->icon ?? 'fas fa-folder' }}"></i>
                    {{ $course->category->name }}
                </span>
                @endif
                <span class="course-hero-badge {{ $course->is_active ? '' : 'bg-secondary' }}">
                    <i class="fas {{ $course->is_active ? 'fa-check-circle' : 'fa-pause-circle' }}"></i>
                    {{ $course->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <h1 class="course-hero-title">{{ $course->course_name }}</h1>
            <p class="course-hero-code">{{ $course->course_code }}</p>

            <div class="course-hero-meta">
                @if($course->instructor)
                <div class="course-hero-meta-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    {{ $course->instructor->full_name }}
                </div>
                @endif
                @if($course->sector)
                <div class="course-hero-meta-item">
                    <i class="fas fa-industry"></i>
                    {{ $course->sector }}
                </div>
                @endif
                @if($course->duration_hours)
                <div class="course-hero-meta-item">
                    <i class="fas fa-clock"></i>
                    {{ $course->duration_hours }} hours
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Course Info Cards --}}
    <div class="course-info-grid">
        {{-- Schedule Card --}}
        @if($course->formatted_schedule || $course->formatted_date_range || $course->duration_hours)
        <div class="course-info-card">
            <div class="course-info-card-header">
                <div class="course-info-card-icon schedule">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h4 class="course-info-card-title">Schedule</h4>
            </div>
            <div class="course-info-card-body">
                @if($course->formatted_date_range)
                <div class="info-row">
                    <i class="fas fa-calendar-day"></i>
                    <span>{{ $course->formatted_date_range }}</span>
                </div>
                @endif
                @if($course->formatted_schedule)
                <div class="info-row">
                    <i class="fas fa-clock"></i>
                    <span>{{ $course->formatted_schedule }}</span>
                </div>
                @endif
                @if($course->duration_hours)
                <div class="info-row">
                    <i class="fas fa-hourglass-half"></i>
                    <span>{{ $course->duration_hours }} hours total</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Instructor Card --}}
        @if($course->instructor)
        <div class="course-info-card">
            <div class="course-info-card-header">
                <div class="course-info-card-icon instructor">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h4 class="course-info-card-title">Instructor</h4>
            </div>
            <div class="course-info-card-body">
                <div class="instructor-profile">
                    <div class="instructor-avatar">
                        @if($course->instructor->profile_photo)
                        <img src="{{ asset('storage/' . $course->instructor->profile_photo) }}" alt="{{ $course->instructor->full_name }}">
                        @else
                        {{ strtoupper(substr($course->instructor->first_name, 0, 1)) }}{{ strtoupper(substr($course->instructor->last_name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="instructor-info">
                        <h4>{{ $course->instructor->full_name }}</h4>
                        <p>{{ $course->instructor->email }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Course Info Card --}}
        <div class="course-info-card">
            <div class="course-info-card-header">
                <div class="course-info-card-icon info">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h4 class="course-info-card-title">About This Course</h4>
            </div>
            <div class="course-info-card-body">
                @if($course->description)
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0; line-height: 1.6;">
                    {{ $course->description }}
                </p>
                @else
                <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">
                    No description provided.
                </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Modules Section --}}
    <div class="modules-section">
        <div class="modules-section-header">
            <h3>
                <i class="fas fa-book"></i>
                Course Modules
            </h3>
            <span class="module-count-badge">{{ $course->modules->count() }}</span>
        </div>

        @if($course->modules->count() > 0)
        <div class="modules-grid">
            @foreach($course->modules as $module)
            <div class="module-card">
                <div class="module-card-header">
                    <div class="module-card-number">{{ $module->module_number }}</div>
                    <h4 class="module-card-name">{{ $module->module_name }}</h4>
                </div>
                <div class="module-card-body">
                    <div class="module-card-title">{{ $module->module_title }}</div>
                    <div class="module-card-meta">
                        <div><strong>Qualification:</strong> {{ $module->qualification_title }}</div>
                        <div><strong>Unit:</strong> {{ Str::limit($module->unit_of_competency, 50) }}</div>
                    </div>
                    <div class="module-card-stat">
                        <i class="fas fa-file-alt"></i>
                        <span>{{ $module->informationSheets->count() }} {{ Str::plural('Information Sheet', $module->informationSheets->count()) }}</span>
                    </div>
                </div>
                <div class="module-card-footer">
                    <div class="module-card-actions">
                        <a href="{{ route('courses.modules.show', [$course, $module]) }}" class="btn btn-category btn-sm">
                            <i class="fas fa-eye me-1"></i>View
                        </a>
                        <a href="{{ route('courses.modules.print', [$course, $module]) }}" class="btn btn-outline-secondary btn-sm" target="_blank" title="Print">
                            <i class="fas fa-print"></i>
                        </a>
                        <a href="{{ route('courses.modules.download', [$course, $module]) }}" class="btn btn-outline-success btn-sm" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>

                    @if(in_array(auth()->user()->role, ['admin', 'instructor']))
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('courses.modules.edit', [$course, $module]) }}">
                                    <i class="fas fa-edit me-2"></i>Edit Module
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('courses.modules.sheets.create', [$course, $module]) }}">
                                    <i class="fas fa-file-alt me-2"></i>Add Content
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('courses.modules.destroy', [$course, $module]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger"
                                        onclick="return confirm('Are you sure? This will delete all associated content.')">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="modules-empty">
            <div class="modules-empty-icon">
                <i class="fas fa-book"></i>
            </div>
            <h4>No Modules Yet</h4>
            <p>This course doesn't have any modules created.</p>
            @if(isset($canEdit) && $canEdit)
            <a href="{{ route('courses.modules.create', $course) }}" class="btn btn-category">
                <i class="fas fa-plus me-2"></i>Create First Module
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection