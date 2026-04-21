@extends('layouts.app')

@section('title', $module->module_number . ' - ' . $module->module_name . ' - EPAS-E')

@push('styles')
<style>
    /* Focus Mode Styles */
    .focus-mode-btn {
        position: fixed;
        bottom: 100px;
        right: 30px;
        z-index: 1050;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .focus-mode-btn:hover {
        transform: scale(1.1);
    }

    /* Focus Mode Active State */
    body.focus-mode-active {
        overflow: hidden;
    }

    body.focus-mode-active .navbar,
    body.focus-mode-active .module-header-section,
    body.focus-mode-active .sidebar-section,
    body.focus-mode-active .progress-card-section,
    body.focus-mode-active .focus-mode-btn {
        display: none !important;
    }

    body.focus-mode-active .main-content-section {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9999;
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }

    /* Focus Mode Container */
    .focus-mode-container {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10000;
        background: #fff;
    }

    .focus-mode-container.active {
        display: flex;
    }

    .focus-mode-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        z-index: 10001;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .focus-mode-header h5 {
        margin: 0;
        font-weight: 600;
    }

    .focus-mode-body {
        display: flex;
        margin-top: 60px;
        height: calc(100vh - 60px);
    }

    /* Left Panel - Images */
    .focus-image-panel {
        width: 40%;
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 30px;
        overflow-y: auto;
    }

    .focus-image-panel .no-image {
        text-align: center;
        color: rgba(255, 255, 255, 0.5);
    }

    .focus-image-panel .no-image i {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    .focus-image-panel img {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .focus-image-panel .image-caption {
        color: white;
        text-align: center;
        margin-top: 15px;
        font-size: 1.1rem;
    }

    /* Image Navigation */
    .image-nav {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .image-nav button {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s;
    }

    .image-nav button:hover {
        background: rgba(255, 255, 255, 0.4);
    }

    .image-counter {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
        margin-top: 10px;
    }

    /* Right Panel - Content */
    .focus-content-panel {
        width: 60%;
        background: #fff;
        padding: 40px 60px;
        overflow-y: auto;
    }

    .focus-content-panel h2 {
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 3px solid #667eea;
    }

    .focus-content-panel .content-body {
        font-size: 1.1rem;
        line-height: 1.9;
        color: #444;
    }

    .focus-content-panel .content-body p {
        margin-bottom: 1.5rem;
    }

    /* Focus Mode Navigation */
    .focus-nav {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 15px;
        z-index: 10002;
    }

    .focus-nav button {
        padding: 12px 30px;
        border: none;
        border-radius: 25px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .focus-nav .btn-prev,
    .focus-nav .btn-next {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .focus-nav .btn-prev:hover,
    .focus-nav .btn-next:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }

    .focus-nav .btn-prev:disabled,
    .focus-nav .btn-next:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    /* Dark mode support for focus mode */
    .dark-mode .focus-content-panel {
        background: #1a1a2e;
    }

    .dark-mode .focus-content-panel h2 {
        color: #e9ecef;
    }

    .dark-mode .focus-content-panel .content-body {
        color: #adb5bd;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .focus-mode-body {
            flex-direction: column;
        }

        .focus-image-panel {
            width: 100%;
            height: 40vh;
            padding: 20px;
        }

        .focus-content-panel {
            width: 100%;
            height: 60vh;
            padding: 20px 30px;
        }

        .focus-image-panel img {
            max-height: 35vh;
        }
    }
</style>
@endpush

@section('content')
<!-- Module Header -->
<div class="container-fluid py-3 bg-white border-bottom mb-4 module-header-section">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-1">{{ $module->module_number }}: {{ $module->module_name }}</h4>
            <p class="text-muted mb-0 small">{{ $module->qualification_title }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-info btn-sm" id="enterFocusMode">
                <i class="fas fa-expand me-1"></i> Focus Mode
            </button>
            <a href="{{ route('modules.print', $module) }}" class="btn btn-outline-secondary btn-sm" target="_blank" title="Print Preview">
                <i class="fas fa-print me-1"></i> Print
            </a>
            <a href="{{ route('modules.download', $module) }}" class="btn btn-outline-success btn-sm" title="Download for Offline">
                <i class="fas fa-download me-1"></i> Download
            </a>
            <button class="btn btn-outline-warning btn-sm" id="saveOfflineBtn" title="Save for Offline Viewing">
                <i class="fas fa-cloud-download-alt me-1"></i> <span id="saveOfflineText">Save Offline</span>
            </button>
            <a href="{{ route('modules.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            @if(Auth::user()->role !== 'student')
            <a href="{{ route('modules.edit', $module->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            @endif
        </div>
    </div>
</div>

<div class="container-fluid py-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 main-content-section">
            <!-- Progress Card -->
            <div class="card border-0 shadow-sm mb-4 progress-card-section">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="position-relative progress-circle-container">
                                <svg viewBox="0 0 100 100" class="progress-circle-svg">
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#e9ecef" stroke-width="8" />
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#ffb902" stroke-width="8"
                                        stroke-dasharray="251.2" stroke-dashoffset="251.2" id="progressCircle" />
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle text-center">
                                    <strong id="progressText">0%</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row text-center">
                                <div class="col-6 col-md-3 mb-2 mb-md-0">
                                    <div class="fw-bold text-primary">{{ $module->informationSheets->count() }}</div>
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
                                    <div class="fw-bold text-warning">0</div>
                                    <small class="text-muted">Completed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div id="contentArea">
                <!-- Overview (Default) -->
                <div class="content-section" id="overviewSection">
                    @if($module->learning_outcomes)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fas fa-bullseye text-primary me-2"></i>Learning Outcomes</h6>
                        </div>
                        <div class="card-body">
                            {!! nl2br(e($module->learning_outcomes)) !!}
                        </div>
                    </div>
                    @endif

                    @if($module->introduction)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fas fa-book-open text-primary me-2"></i>Introduction</h6>
                        </div>
                        <div class="card-body">
                            {!! nl2br(e($module->introduction)) !!}
                        </div>
                    </div>
                    @endif

                    @if($module->how_to_use_cblm)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i>How to Use This CBLM</h6>
                        </div>
                        <div class="card-body">
                            {!! nl2br(e($module->how_to_use_cblm)) !!}
                        </div>
                    </div>
                    @endif

                    <!-- Module Details -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fas fa-info text-primary me-2"></i>Module Details</h6>
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
                </div>

                <!-- Dynamic Sheet Content -->
                <div id="sheetContent" style="display: none;"></div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 sidebar-section">
            <!-- Table of Contents -->
            <div class="card border-0 shadow-sm sticky-top sticky-sidebar-offset">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Table of Contents</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <!-- Overview -->
                        <a href="#" class="list-group-item list-group-item-action active toc-link" data-section="overview">
                            <i class="fas fa-home me-2"></i> Module Overview
                        </a>

                        <!-- Information Sheets -->
                        @foreach($module->informationSheets as $sheet)
                        <div class="toc-group">
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center toc-link sheet-link"
                                data-sheet-id="{{ $sheet->id }}">
                                <span>
                                    <i class="fas fa-file-alt me-2"></i>
                                    Info Sheet {{ $sheet->sheet_number }}
                                </span>
                                <i class="fas fa-chevron-down small toggle-icon"></i>
                            </a>
                            <div class="toc-subitems bg-light" style="display: none;">
                                <a href="#" class="list-group-item list-group-item-action small ps-5 toc-sublink"
                                    data-sheet-id="{{ $sheet->id }}" data-action="content">
                                    <i class="fas fa-align-left me-2"></i> {{ Str::limit($sheet->title, 25) }}
                                </a>
                                @if($sheet->topics && $sheet->topics->count() > 0)
                                @foreach($sheet->topics as $topic)
                                <a href="#" class="list-group-item list-group-item-action small ps-5 toc-sublink"
                                    data-topic-id="{{ $topic->id }}" data-sheet-id="{{ $sheet->id }}">
                                    <i class="fas fa-circle fa-xs me-2"></i> {{ Str::limit($topic->title, 25) }}
                                </a>
                                @endforeach
                                @endif
                                <a href="{{ route('modules.information-sheets.self-check', [$module->id, $sheet->id]) }}"
                                    class="list-group-item list-group-item-action small ps-5 text-warning">
                                    <i class="fas fa-question-circle me-2"></i> Self-Check
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Focus Mode Floating Button -->
<button class="btn btn-primary focus-mode-btn" id="focusModeFloatingBtn" title="Enter Focus Mode">
    <i class="fas fa-expand"></i>
</button>

<!-- Focus Mode Container -->
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
    <div class="focus-nav">
        <button class="btn-prev" id="focusPrevBtn"><i class="fas fa-arrow-left me-2"></i>Previous</button>
        <button class="btn-next" id="focusNextBtn">Next<i class="fas fa-arrow-right ms-2"></i></button>
    </div>
</div>

<!-- Data for JS -->
<div id="moduleData" data-module-id="{{ $module->id }}" data-csrf="{{ csrf_token() }}" style="display: none;"></div>

<!-- Focus Mode Content Data -->
<script type="application/json" id="focusModeData">
    @php
    $focusContent = [];

    // Add module overview
    $focusContent[] = [
        'type' => 'overview',
        'title' => 'Module Overview: '.$module->module_name,
        'content' => $module->introduction ?? $module->learning_outcomes ?? 'Welcome to '.$module->module_name,
        'images' => $module->images ?? []
    ];

    // Add information sheets and their topics
    foreach($module->informationSheets as $sheet) {
        $focusContent[] = [
            'type' => 'sheet',
            'id' => $sheet->id,
            'title' => 'Info Sheet '.$sheet->sheet_number.
            ': '.$sheet->title,
            'content' => $sheet->content ?? '',
            'images' => $sheet->parts ? collect($sheet->parts)->pluck('image')->filter()->values()->toArray() : []
        ];

        if ($sheet->topics) {
            foreach($sheet->topics as $topic) {
                $topicImages = [];
                if ($topic->parts) {
                    foreach($topic->parts as $part) {
                        if (!empty($part['image'])) {
                            $topicImages[] = [
                                'url' => $part['image'],
                                'caption' => $part['title'] ?? ''
                            ];
                        }
                    }
                }

                $focusContent[] = [
                    'type' => 'topic',
                    'id' => $topic->id,
                    'sheetId' => $sheet->id,
                    'title' => $topic->title,
                    'content' => $topic->content ?? '',
                    'document_content' => $topic->document_content ?? '',
                    'parts' => $topic->parts ?? [],
                    'images' => $topicImages
                ];
            }
        }
    }
    @endphp
    @json($focusContent)
</script>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const moduleData = document.getElementById('moduleData');
        const csrfToken = moduleData.dataset.csrf;

        // TOC Navigation - Sheet links
        document.querySelectorAll('.sheet-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const sheetId = this.dataset.sheetId;
                const subitems = this.nextElementSibling;
                const icon = this.querySelector('.toggle-icon');

                // Toggle subitems
                if (subitems) {
                    const isVisible = subitems.style.display !== 'none';
                    subitems.style.display = isVisible ? 'none' : 'block';
                    icon.classList.toggle('fa-chevron-down', isVisible);
                    icon.classList.toggle('fa-chevron-up', !isVisible);
                }

                // Load content
                loadSheetContent(sheetId);
                setActiveLink(this);
            });
        });

        // TOC Sub-links
        document.querySelectorAll('.toc-sublink').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const sheetId = this.dataset.sheetId;
                const topicId = this.dataset.topicId;

                if (topicId) {
                    loadTopicContent(sheetId, topicId);
                } else {
                    loadSheetContent(sheetId);
                }
                setActiveSublink(this);
            });
        });

        // Overview link
        document.querySelector('[data-section="overview"]').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('overviewSection').style.display = 'block';
            document.getElementById('sheetContent').style.display = 'none';
            setActiveLink(this);
        });

        function setActiveLink(element) {
            document.querySelectorAll('.toc-link').forEach(l => l.classList.remove('active'));
            element.classList.add('active');
        }

        function setActiveSublink(element) {
            document.querySelectorAll('.toc-sublink').forEach(l => l.classList.remove('active'));
            element.classList.add('active');
        }

        function loadSheetContent(sheetId) {
            const contentArea = document.getElementById('sheetContent');
            contentArea.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted">Loading...</p></div>';
            contentArea.style.display = 'block';
            document.getElementById('overviewSection').style.display = 'none';

            fetch(`/modules/information-sheets/${sheetId}/content`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        contentArea.innerHTML = data.html;
                    } else {
                        contentArea.innerHTML = '<div class="alert alert-warning">Could not load content.</div>';
                    }
                })
                .catch(() => {
                    contentArea.innerHTML = '<div class="alert alert-danger">Failed to load content.</div>';
                });
        }

        function loadTopicContent(sheetId, topicId) {
            const contentArea = document.getElementById('sheetContent');
            contentArea.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>';
            contentArea.style.display = 'block';
            document.getElementById('overviewSection').style.display = 'none';

            fetch(`/modules/information-sheets/${sheetId}/topics/${topicId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        contentArea.innerHTML = data.html;
                    }
                })
                .catch(() => {
                    contentArea.innerHTML = '<div class="alert alert-danger">Failed to load topic.</div>';
                });
        }

        // Progress circle animation
        const progress = 0; // TODO: Calculate from user progress
        const circle = document.getElementById('progressCircle');
        if (circle) {
            const circumference = 2 * Math.PI * 40;
            const offset = circumference - (progress / 100) * circumference;
            circle.style.strokeDasharray = circumference;
            circle.style.strokeDashoffset = offset;
            document.getElementById('progressText').textContent = progress + '%';
        }

        // ==================== FOCUS MODE ====================
        const focusModeContainer = document.getElementById('focusModeContainer');
        const focusModeData = JSON.parse(document.getElementById('focusModeData').textContent);
        let currentFocusIndex = 0;
        let currentImageIndex = 0;

        function enterFocusMode() {
            document.body.classList.add('focus-mode-active');
            focusModeContainer.classList.add('active');
            updateFocusContent();

            // Keyboard navigation
            document.addEventListener('keydown', focusKeyHandler);
        }

        function exitFocusMode() {
            document.body.classList.remove('focus-mode-active');
            focusModeContainer.classList.remove('active');
            document.removeEventListener('keydown', focusKeyHandler);
        }

        function focusKeyHandler(e) {
            if (e.key === 'Escape') {
                exitFocusMode();
            } else if (e.key === 'ArrowRight' || e.key === ' ') {
                e.preventDefault();
                nextFocusContent();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                prevFocusContent();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                prevImage();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                nextImage();
            }
        }

        function updateFocusContent() {
            const content = focusModeData[currentFocusIndex];
            if (!content) return;

            // Update title
            document.getElementById('focusModeTitle').textContent = content.title;
            document.getElementById('focusContentTitle').textContent = content.title;

            // Update content
            let bodyHtml = '';
            if (content.content) {
                bodyHtml += `<div class="mb-4">${content.content.replace(/\n/g, '<br>')}</div>`;
            }

            // Add document content from uploaded files
            if (content.document_content) {
                bodyHtml += `<div class="mb-4 document-content">${content.document_content}</div>`;
            }

            // Add parts if available
            if (content.parts && content.parts.length > 0) {
                content.parts.forEach((part, idx) => {
                    bodyHtml += `
                    <div class="part-section mb-4 p-3 bg-light rounded">
                        <h5><span class="badge bg-primary me-2">${idx + 1}</span>${part.title || ''}</h5>
                        <p>${(part.explanation || '').replace(/\n/g, '<br>')}</p>
                    </div>
                `;
                });
            }

            document.getElementById('focusContentBody').innerHTML = bodyHtml || '<p class="text-muted">No content available for this section.</p>';

            // Update images
            currentImageIndex = 0;
            updateFocusImage(content);

            // Update progress badge
            document.getElementById('focusProgressBadge').textContent = `${currentFocusIndex + 1} / ${focusModeData.length}`;

            // Update navigation buttons
            document.getElementById('focusPrevBtn').disabled = currentFocusIndex === 0;
            document.getElementById('focusNextBtn').disabled = currentFocusIndex === focusModeData.length - 1;
        }

        function updateFocusImage(content) {
            const images = content.images || [];
            const noImage = document.getElementById('focusNoImage');
            const focusImage = document.getElementById('focusImage');
            const imageCaption = document.getElementById('focusImageCaption');
            const imageNav = document.getElementById('imageNav');
            const imageCounter = document.getElementById('imageCounter');

            if (images.length === 0) {
                noImage.style.display = 'block';
                focusImage.style.display = 'none';
                imageNav.style.display = 'none';
                imageCaption.textContent = '';
                imageCounter.textContent = '';
            } else {
                noImage.style.display = 'none';
                focusImage.style.display = 'block';

                const img = images[currentImageIndex];
                focusImage.src = typeof img === 'string' ? img : (img.url || img);
                imageCaption.textContent = typeof img === 'object' ? (img.caption || '') : '';

                if (images.length > 1) {
                    imageNav.style.display = 'flex';
                    imageCounter.textContent = `Image ${currentImageIndex + 1} of ${images.length}`;
                } else {
                    imageNav.style.display = 'none';
                    imageCounter.textContent = '';
                }
            }
        }

        function nextFocusContent() {
            if (currentFocusIndex < focusModeData.length - 1) {
                currentFocusIndex++;
                updateFocusContent();
            }
        }

        function prevFocusContent() {
            if (currentFocusIndex > 0) {
                currentFocusIndex--;
                updateFocusContent();
            }
        }

        function nextImage() {
            const content = focusModeData[currentFocusIndex];
            const images = content.images || [];
            if (currentImageIndex < images.length - 1) {
                currentImageIndex++;
                updateFocusImage(content);
            }
        }

        function prevImage() {
            if (currentImageIndex > 0) {
                currentImageIndex--;
                updateFocusImage(focusModeData[currentFocusIndex]);
            }
        }

        // Event Listeners for Focus Mode
        document.getElementById('enterFocusMode').addEventListener('click', enterFocusMode);
        document.getElementById('focusModeFloatingBtn').addEventListener('click', enterFocusMode);
        document.getElementById('exitFocusMode').addEventListener('click', exitFocusMode);
        document.getElementById('focusPrevBtn').addEventListener('click', prevFocusContent);
        document.getElementById('focusNextBtn').addEventListener('click', nextFocusContent);
        document.getElementById('prevImage').addEventListener('click', prevImage);
        document.getElementById('nextImage').addEventListener('click', nextImage);

        // ==================== OFFLINE SAVE ====================
        const saveOfflineBtn = document.getElementById('saveOfflineBtn');
        const saveOfflineText = document.getElementById('saveOfflineText');
        const moduleId = moduleData.dataset.moduleId;

        // Check if module is already cached
        if ('caches' in window) {
            caches.has(`module-${moduleId}`).then(cached => {
                if (cached) {
                    saveOfflineText.textContent = 'Saved';
                    saveOfflineBtn.classList.remove('btn-outline-warning');
                    saveOfflineBtn.classList.add('btn-warning');
                }
            });
        }

        saveOfflineBtn.addEventListener('click', async function() {
            if (!('serviceWorker' in navigator) || !navigator.serviceWorker.controller) {
                alert('Offline mode requires service worker support. Please refresh the page.');
                return;
            }

            // Show loading state
            saveOfflineBtn.disabled = true;
            saveOfflineText.textContent = 'Saving...';
            saveOfflineBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            try {
                const result = await window.cacheModuleForOffline(moduleId, window.location.href);

                if (result.success) {
                    saveOfflineBtn.innerHTML = '<i class="fas fa-check me-1"></i> Saved';
                    saveOfflineBtn.classList.remove('btn-outline-warning');
                    saveOfflineBtn.classList.add('btn-success');

                    // Show success notification
                    showNotification('Module saved for offline viewing!', 'success');
                } else {
                    throw new Error(result.error || 'Failed to save');
                }
            } catch (error) {
                console.error('Offline save error:', error);
                saveOfflineBtn.innerHTML = '<i class="fas fa-cloud-download-alt me-1"></i> Save Offline';
                showNotification('Failed to save module for offline viewing.', 'error');
            } finally {
                saveOfflineBtn.disabled = false;
            }
        });

        function showNotification(message, type) {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
            toast.style.cssText = 'bottom: 20px; right: 20px; z-index: 9999; animation: fadeIn 0.3s;';
            toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}`;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    });
</script>
@endpush