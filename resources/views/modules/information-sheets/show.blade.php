<!-- resources/views/modules/show.blade.php -->
@extends('layouts.app')

@section('title', $module->module_number . ' - ' . $module->module_name . ' - EPAS-E')

@section('content')

<!-- Module Header - Sticky Secondary Nav -->
<header class="module-header">
    <div class="module-title">
        <h1>{{ $module->module_number }}: {{ $module->module_name }}</h1>
        <p>{{ $module->qualification_title }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('courses.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Content Management
        </a>
    </div>
</header>

<button class="btn btn-primary mobile-toc-toggle" id="mobileTocToggle">
    <i class="fas fa-list"></i>
</button>

<div class="module-container">
    <!-- Main Content -->
    <main class="module-main">
        <div class="content-wrapper">
            <!-- Dynamic Content Area -->
            <div id="dynamic-content">
                <!-- Overview Section (Default) -->
                <section id="overview" class="content-section section-transition active-section">
                    <div class="section-header">
                        <h2>Module Overview</h2>
                        <p>Get started with {{ $module->module_name }}</p>
                    </div>

                    <!-- Module Details -->
                    <div class="info-card">
                        <h3><i class="fas fa-info-circle"></i> Module Details</h3>
                        <div class="details-grid">
                            <div>
                                <strong>Sector:</strong> {{ $module->sector }}
                            </div>
                            <div>
                                <strong>Module Number:</strong> {{ $module->module_number }}
                            </div>
                            <div>
                                <strong>Qualification Title:</strong> {{ $module->qualification_title }}
                            </div>
                            <div>
                                <strong>Unit of Competency:</strong> {{ $module->unit_of_competency }}
                            </div>
                        </div>
                    </div>

                    <!-- Introduction Content -->
                    @if($module->learning_outcomes || $module->how_to_use_cblm || $module->introduction)
                    <div class="info-card">
                        <h3><i class="fas fa-book-open"></i> Module Introduction</h3>
                        
                        @if($module->learning_outcomes)
                        <div class="content-display">
                            <h4>Learning Outcomes</h4>
                            {!! nl2br(e($module->learning_outcomes)) !!}
                        </div>
                        @endif

                        @if($module->how_to_use_cblm)
                        <div class="content-display">
                            <h4>How to Use This CBLM</h4>
                            {!! nl2br(e($module->how_to_use_cblm)) !!}
                        </div>
                        @endif

                        @if($module->introduction)
                        <div class="content-display">
                            <h4>Introduction</h4>
                            {!! nl2br(e($module->introduction)) !!}
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Start Learning Button -->
                    @if($module->informationSheets->count() > 0)
                    <div class="start-learning-section text-center mt-5">
                        <button class="btn btn-primary btn-lg start-learning-btn" 
                                data-sheet-id="{{ $module->informationSheets->first()->id }}">
                            <i class="fas fa-play me-2"></i>Start Learning
                        </button>
                    </div>
                    @endif
                </section>
            </div>
        </div>
    </main>

    <!-- Right Sidebar -->
    <aside class="module-sidebar" id="moduleTocSidebar">
        <!-- Progress Section -->
        <div class="progress-container">
            <div class="progress-circle">
                <svg viewBox="0 0 100 100">
                    <circle class="progress-bg" cx="50" cy="50" r="45"/>
                    <circle class="progress-fill" cx="50" cy="50" r="45" id="progressCircle"/>
                </svg>
                <div class="progress-text" id="progressText">0%</div>
            </div>
            <div>
                <div class="progress-label">Overall Progress</div>
                <small class="progress-subtitle">{{ $module->informationSheets->count() }} information sheets</small>
            </div>
        </div>

        <!-- Current Section -->
        <div class="info-card current-section">
            <div class="current-section-label">CURRENT SECTION</div>
            <div class="current-section-title" id="currentSection">Module Overview</div>
        </div>

        <!-- Navigation Buttons -->
        <div class="navigation-buttons">
            <button class="btn btn-outline" id="sidebar-prev" disabled>
                <i class="fas fa-arrow-left"></i> Previous
            </button>
            <button class="btn btn-primary" id="sidebar-next">
                Next <i class="fas fa-arrow-right"></i>
            </button>
        </div>

        <!-- Module Actions -->
        <div class="module-actions">
            <a href="#" class="action-btn" id="download-pdf">
                <i class="fas fa-download"></i> Download Current PDF
            </a>
        </div>

        <!-- Table of Contents -->
        <div class="toc-section">
            <div class="toc-title">
                <i class="fas fa-list"></i> Table of Contents
            </div>
            
            <!-- Overview Item -->
            <div class="toc-item">
                <div class="toc-link active" data-content="overview">
                    <i class="fas fa-home toc-icon"></i>
                    Module Overview
                </div>
            </div>

            <!-- Information Sheets with Dropdown -->
            @foreach($module->informationSheets as $infoSheet)
            <div class="toc-item information-sheet-item">
                <div class="toc-link sheet-header" data-sheet-id="{{ $infoSheet->id }}">
                    <i class="fas fa-chevron-down toc-icon toggle-icon"></i>
                    <div class="sheet-title">
                        <div class="sheet-main-title">Information Sheet {{ $infoSheet->sheet_number }}</div>
                        <div class="sheet-subtitle">{{ $infoSheet->title }}</div>
                    </div>
                </div>
                
                <!-- Topics Dropdown -->
                <div class="topics-dropdown">
                    @if($infoSheet->topics && $infoSheet->topics->count() > 0)
                        @foreach($infoSheet->topics as $topic)
                        <div class="topic-item" data-topic-id="{{ $topic->id }}">
                            <i class="fas fa-file-alt topic-icon"></i>
                            <span class="topic-title">{{ $topic->title }}</span>
                        </div>
                        @endforeach
                    @endif

                    {{-- Self-Checks (direct links) --}}
                    @if($infoSheet->selfChecks && $infoSheet->selfChecks->count() > 0)
                        @foreach($infoSheet->selfChecks as $sc)
                        <a href="{{ route('self-checks.show', $sc) }}" class="topic-item" style="text-decoration: none; color: inherit;">
                            <i class="fas fa-clipboard-check topic-icon" style="color: #ffc107;"></i>
                            <span class="topic-title">{{ $sc->title }}</span>
                        </a>
                        @endforeach
                    @endif

                    {{-- Document Assessments (direct links) --}}
                    @if($infoSheet->documentAssessments && $infoSheet->documentAssessments->count() > 0)
                        @foreach($infoSheet->documentAssessments as $da)
                        <a href="{{ route('document-assessments.show', $da) }}" class="topic-item" style="text-decoration: none; color: inherit;">
                            <i class="fas fa-file-word topic-icon" style="color: #6f42c1;"></i>
                            <span class="topic-title">{{ $da->title }}</span>
                        </a>
                        @endforeach
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </aside>
</div>

<!-- Hidden data container for JavaScript -->
<div id="module-data"
     data-total-sections="{{ $module->informationSheets->count() + 1 }}"
     data-info-sheets="{{ json_encode($module->informationSheets->pluck('id')) }}"
     data-module-id="{{ $module->id }}"
     data-user-role="{{ auth()->user()->role }}"
     data-csrf-token="{{ csrf_token() }}"
     style="display: none;">
</div>
@endsection

@push('styles')
<style>
:root {
    --primary: #ffb902;
    --secondary: #6c757d;
    --success: #198754;
    --info: #0dcaf0;
    --warning: #ffc107;
    --danger: #dc3545;
    --light: #f8f9fa;
    --dark: #212529;
    --gray: #6c757d;
}

.module-header {
    background: #6d9773;
    color: white;
    padding: 2rem;
    margin: -1rem -1rem 2rem -1rem;
    display: flex;
    justify-content: between;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.module-title h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
}

.module-title p {
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
}

.header-actions .btn-outline {
    border-color: white;
    color: white;
}

.header-actions .btn-outline:hover {
    background: white;
    color: var(--primary);
}

.mobile-toc-toggle {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1000;
    display: none;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.module-container {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    min-height: calc(100vh - 200px);
}

.module-main {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 2rem;
}

.module-sidebar {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 1.5rem;
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.progress-container {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.progress-circle {
    position: relative;
    width: 60px;
    height: 60px;
}

.progress-bg {
    fill: none;
    stroke: #e9ecef;
    stroke-width: 8;
}

.progress-fill {
    fill: none;
    stroke: var(--success);
    stroke-width: 8;
    stroke-linecap: round;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
    transition: stroke-dasharray 0.3s ease;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--dark);
}

.progress-label {
    font-weight: 600;
    color: var(--dark);
}

.progress-subtitle {
    color: var(--gray);
}

.current-section {
    margin-bottom: 1.5rem;
}

.current-section-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 0.25rem;
}

.current-section-title {
    font-weight: 600;
    color: var(--dark);
}

.navigation-buttons {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.navigation-buttons .btn {
    flex: 1;
}

.module-actions {
    margin-bottom: 1.5rem;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: var(--light);
    border: 1px solid #dee2e6;
    border-radius: 8px;
    color: var(--dark);
    text-decoration: none;
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: var(--primary);
    color: white;
    text-decoration: none;
}

.toc-title {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.toc-item {
    margin-bottom: 0.5rem;
}

.toc-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: var(--light);
    border: 1px solid #dee2e6;
    border-radius: 8px;
    color: var(--dark);
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.toc-link.active,
.toc-link:hover {
    background: var(--primary);
    color: white;
    text-decoration: none;
}

.toc-icon {
    width: 16px;
    text-align: center;
}

.sheet-title {
    flex: 1;
}

.sheet-main-title {
    font-weight: 600;
    font-size: 0.9rem;
}

.sheet-subtitle {
    font-size: 0.8rem;
    opacity: 0.8;
    margin-top: 0.1rem;
}

.topics-dropdown {
    display: none;
    margin-top: 0.5rem;
    margin-left: 1rem;
}

.information-sheet-item.expanded .topics-dropdown {
    display: block;
}

.information-sheet-item.expanded .toggle-icon {
    transform: rotate(180deg);
}

.topic-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 1rem;
    margin-bottom: 0.25rem;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.topic-item:hover,
.topic-item.active {
    background: var(--primary);
    color: white;
}

.topic-icon {
    width: 14px;
    text-align: center;
    font-size: 0.8rem;
}

.topic-title {
    font-size: 0.85rem;
}

.info-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.info-card h3 {
    color: var(--primary);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.content-display {
    margin-bottom: 1.5rem;
}

.content-display h4 {
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.content-display:last-child {
    margin-bottom: 0;
}

.start-learning-section {
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    border: 2px dashed #dee2e6;
}

.section-header {
    text-align: center;
    margin-bottom: 2rem;
}

.section-header h2 {
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.section-header p {
    color: var(--gray);
    font-size: 1.1rem;
}

/* Responsive Design */
@media (max-width: 1032px) {
    .module-container {
        grid-template-columns: 1fr;
    }
    
    .module-sidebar {
        display: none;
    }
    
    .mobile-toc-toggle {
        display: block;
    }
    
    .module-sidebar.mobile-visible {
        display: block;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 999;
        border-radius: 0;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script src="{{ dynamic_asset('js/modules/show.js') }}"></script>
@endpush