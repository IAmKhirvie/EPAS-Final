@extends('layouts.app')

@section('title', 'Courses - EPAS-E')

@push('styles')
<link rel="stylesheet" href="{{ dynamic_asset('css/pages/courses.css') }}?v={{ filemtime(public_path('css/pages/courses.css')) }}">
<style>
    /* Courses Page Layout */
    .courses-page-wrapper {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1.5rem;
        min-height: calc(100vh - 200px);
        overflow-x: hidden;
    }

    /* Right Sidebar */
    .courses-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .sidebar-widget {
        background: var(--surface);
        border-radius: var(--border-radius);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .sidebar-widget-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sidebar-widget-header h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sidebar-widget-header h3 i {
        color: var(--primary);
    }

    .sidebar-widget-body {
        padding: 1rem 1.25rem;
    }

    /* Mini Calendar */
    .mini-calendar {
        width: 100%;
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .calendar-header h4 {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .calendar-nav {
        display: flex;
        gap: 0.25rem;
    }

    .calendar-nav button {
        width: 28px;
        height: 28px;
        border: none;
        background: transparent;
        color: var(--text-muted);
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .calendar-nav button:hover {
        background: var(--primary);
        color: white;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        text-align: center;
    }

    .calendar-day-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-muted);
        padding: 0.5rem 0;
    }

    .calendar-day {
        position: relative;
        padding: 0.4rem;
        padding-bottom: 0.6rem;
        font-size: 0.8rem;
        color: var(--text-secondary);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .calendar-day:hover {
        background: var(--primary-light);
        color: white;
    }

    .calendar-day.today {
        background: var(--primary);
        color: white;
        font-weight: 700;
    }

    .calendar-day.other-month {
        color: var(--text-muted);
        opacity: 0.5;
    }

    /* Calendar event dots */
    .calendar-day.has-events {
        cursor: pointer;
    }

    .calendar-event-dots {
        position: absolute;
        bottom: 2px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 2px;
        justify-content: center;
    }

    .event-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .calendar-day.today .event-dot {
        box-shadow: 0 0 0 1px white;
    }

    /* Upcoming Tasks */
    .upcoming-tasks-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .upcoming-task-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--background);
        border-radius: calc(var(--border-radius) / 2);
        transition: all 0.2s ease;
    }

    .upcoming-task-item:hover {
        background: var(--primary);
        color: white;
    }

    .upcoming-task-item:hover .task-course,
    .upcoming-task-item:hover .task-due {
        color: rgba(255, 255, 255, 0.8);
    }

    .task-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.85rem;
    }

    .task-icon.quiz {
        background: rgba(139, 92, 246, 0.15);
        color: #8b5cf6;
    }

    .task-icon.assignment {
        background: rgba(255, 185, 2, 0.15);
        color: #ffb902;
    }

    .task-icon.deadline {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }

    .task-info {
        flex: 1;
        min-width: 0;
    }

    .task-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.15rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .task-course {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .task-due {
        font-size: 0.7rem;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .upcoming-task-item:hover .task-title {
        color: white;
    }

    .no-tasks {
        text-align: center;
        padding: 1.5rem;
        color: var(--text-muted);
    }

    .no-tasks i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        opacity: 0.5;
    }

    .no-tasks p {
        margin: 0;
        font-size: 0.85rem;
    }

    /* Category Stats Widget */
    .category-stats {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .category-stat-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
        cursor: pointer;
        transition: all 0.2s ease;
        border-radius: calc(var(--border-radius) / 2);
    }

    .category-stat-item:hover {
        padding-left: 0.5rem;
        background: var(--background);
    }

    .category-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .category-name {
        flex: 1;
        font-size: 0.85rem;
        color: var(--text-primary);
    }

    .category-count {
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* Main Content Area */
    .courses-main {
        display: flex;
        flex-direction: column;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .courses-page-wrapper {
            grid-template-columns: 1fr;
        }

        .courses-sidebar {
            display: none;
        }
    }

    /* Dark mode */
    .dark-mode .sidebar-widget {
        background: var(--surface);
        border-color: var(--border);
    }

    .dark-mode .upcoming-task-item {
        background: rgba(255, 255, 255, 0.05);
    }

    .dark-mode .calendar-day:hover {
        background: var(--primary);
    }

    /* Category Filter Tabs */
    .category-tabs {
        display: flex;
        gap: 0.5rem;
        flex-wrap: nowrap;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 0.25rem;
    }

    .category-tabs::-webkit-scrollbar {
        display: none;
    }

    .category-tab {
        padding: 0.5rem 1rem;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-secondary);
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .category-tab:hover {
        border-color: var(--tab-color, var(--primary));
        color: var(--tab-color, var(--primary));
    }

    .category-tab.active {
        background: var(--tab-color, var(--primary));
        border-color: var(--tab-color, var(--primary));
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="courses-header">
        <h1><i class="fas fa-graduation-cap me-2"></i>My Courses</h1>
        @if(in_array(auth()->user()->role, ['admin', 'instructor']))
        <div class="courses-header-actions">
            <a href="{{ route('courses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Course
            </a>
        </div>
        @endif
    </div>

    {{-- Management Mode Notice --}}
    @if(Request::get('manage'))
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        You are in management mode.
        <a href="{{ route('courses.index') }}" class="btn btn-sm btn-outline-info ms-3">Back to Normal View</a>
    </div>
    @endif

    <div class="courses-page-wrapper">
        {{-- Main Content --}}
        <div class="courses-main">
            {{-- Toolbar: Search & View Toggle --}}
            <div class="courses-toolbar">
                <div class="courses-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="courseSearch" placeholder="Search courses..." autocomplete="off">
                </div>

                {{-- Category Filter Tabs --}}
                <div class="category-tabs">
                    <button class="category-tab active" data-category="all">All</button>
                    @foreach($categories as $category)
                    <button class="category-tab" data-category="{{ $category->id }}" style="--tab-color: {{ $category->color }}">
                        {{ $category->name }}
                    </button>
                    @endforeach
                </div>

                <div class="view-toggle">
                    <button id="gridViewBtn" class="active" title="Grid View">
                        <i class="fas fa-th-large"></i>
                        <span class="d-none d-sm-inline">Grid</span>
                    </button>
                    <button id="listViewBtn" title="List View">
                        <i class="fas fa-list"></i>
                        <span class="d-none d-sm-inline">List</span>
                    </button>
                </div>
            </div>

            {{-- Courses Container --}}
            @if($courses->count() > 0)
            <div class="courses-container grid-view" id="coursesContainer">
                @foreach($courses as $course)
                @php
                $categoryColor = $course->category?->color ?? '#6d9773';
                $categoryColorDark = $course->category?->darker_color ?? '#0c3a2d';
                $thumbnailUrl = $course->thumbnail ? asset('storage/' . $course->thumbnail) : '';
                @endphp
                <div class="course-card {{ $course->thumbnail ? 'has-thumbnail' : '' }}"
                    data-course-name="{{ strtolower($course->course_name) }}"
                    data-course-code="{{ strtolower($course->course_code) }}"
                    data-category="{{ $course->category_id ?? '' }}"
                    style="--category-color: {{ $categoryColor }}; --category-color-dark: {{ $categoryColorDark }}; {{ $thumbnailUrl ? '--bg-image: url(' . $thumbnailUrl . ');' : '' }}">

                    {{-- Background Overlay --}}
                    <div class="course-card-bg"></div>

                    {{-- Top Bar: Menu & Badges --}}
                    <div class="course-card-top">
                        {{-- Category & Module Badge --}}
                        <div class="course-card-badges">
                            @if($course->category)
                            <span class="course-badge category-badge">
                                <i class="{{ $course->category->icon ?? 'fas fa-folder' }}"></i>
                                {{ $course->category->name }}
                            </span>
                            @endif
                            <span class="course-badge module-badge">
                                <i class="fas fa-book"></i>
                                {{ $course->modules_count }} {{ Str::plural('Module', $course->modules_count) }}
                            </span>
                        </div>

                        {{-- Menu Button --}}
                        @if(in_array(auth()->user()->role, ['admin', 'instructor']))
                        <div class="course-card-menu">
                            <div class="dropdown">
                                <button class="course-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('courses.edit', $course) }}">
                                            <i class="fas fa-edit me-2"></i>Edit Course
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('courses.modules.create', $course) }}">
                                            <i class="fas fa-plus me-2"></i>Add Module
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="{{ route('courses.destroy', $course) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"
                                                onclick="return confirm('Are you sure you want to delete this course?')">
                                                <i class="fas fa-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Card Content (centered) --}}
                    <div class="course-card-body">
                        {{-- Course Code --}}
                        <span class="course-code-badge">{{ $course->course_code }}</span>

                        {{-- Course Title --}}
                        <h3 class="course-card-title">{{ $course->course_name }}</h3>

                        {{-- Instructor Name --}}
                        @if($course->instructor)
                        <div class="course-instructor-line">
                            <div class="instructor-avatar-sm">
                                @if($course->instructor->profile_photo)
                                <img src="{{ asset('storage/' . $course->instructor->profile_photo) }}" alt="{{ $course->instructor->full_name }}">
                                @else
                                {{ strtoupper(substr($course->instructor->first_name, 0, 1)) }}{{ strtoupper(substr($course->instructor->last_name, 0, 1)) }}
                                @endif
                            </div>
                            <span>{{ $course->instructor->full_name }}</span>
                        </div>
                        @endif

                        {{-- Description --}}
                        @if($course->description)
                        <p class="course-card-description">{{ Str::limit($course->description, 80) }}</p>
                        @endif
                    </div>

                    {{-- Card Footer --}}
                    <div class="course-card-footer">
                        <div class="course-stats-row">
                            @if($course->formatted_date_range)
                            <div class="stat-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>{{ $course->formatted_date_range }}</span>
                            </div>
                            @endif
                            @if($course->duration_hours)
                            <div class="stat-item">
                                <i class="fas fa-clock"></i>
                                <span>{{ $course->duration_hours }}h</span>
                            </div>
                            @endif
                            @if($course->sector && !$course->formatted_date_range && !$course->duration_hours)
                            <div class="stat-item">
                                <i class="fas fa-industry"></i>
                                <span>{{ Str::limit($course->sector, 20) }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="course-card-actions">
                            @if(in_array(auth()->user()->role, ['admin', 'instructor']))
                            <a href="{{ route('courses.edit', $course) }}" class="btn btn-edit-course" title="Edit Course">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                            <a href="{{ route('courses.show', $course) }}" class="btn btn-view-course">
                                View <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- No Results Message --}}
            <div class="courses-empty" id="noResults" style="display: none;">
                <div class="courses-empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>No Courses Found</h3>
                <p>No courses match your search criteria.</p>
            </div>
            @else
            {{-- Empty State --}}
            <div class="courses-empty">
                <div class="courses-empty-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>No Courses Available</h3>
                <p>No learning courses have been created yet.</p>
                @if(in_array(auth()->user()->role, ['admin', 'instructor']))
                <a href="{{ route('courses.create') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>Create Your First Course
                </a>
                @endif
            </div>
            @endif
        </div>

        {{-- Right Sidebar --}}
        <aside class="courses-sidebar">
            {{-- Mini Calendar --}}
            <div class="sidebar-widget">
                <div class="sidebar-widget-header">
                    <h3><i class="fas fa-calendar-alt"></i> Calendar</h3>
                </div>
                <div class="sidebar-widget-body">
                    <div class="mini-calendar">
                        <div class="calendar-header">
                            <h4 id="calendarMonth">{{ now()->format('F Y') }}</h4>
                            <div class="calendar-nav">
                                <button id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                                <button id="nextMonth"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="calendar-grid" id="calendarGrid">
                            {{-- Calendar days will be populated by JS --}}
                        </div>
                        <div class="calendar-legend" style="margin-top: 8px; font-size: 0.7rem; display: flex; flex-wrap: wrap; gap: 6px 12px; padding: 0 4px;">
                            <span style="display:flex;align-items:center;gap:3px;"><i class="fas fa-circle" style="font-size:6px;color:#4cc9f0;"></i> Course</span>
                            <span style="display:flex;align-items:center;gap:3px;"><i class="fas fa-circle" style="font-size:6px;color:#f72585;"></i> Self-Check</span>
                            <span style="display:flex;align-items:center;gap:3px;"><i class="fas fa-circle" style="font-size:6px;color:#7209b7;"></i> Homework</span>
                            <span style="display:flex;align-items:center;gap:3px;"><i class="fas fa-circle" style="font-size:6px;color:#ffb902;"></i> Test</span>
                            <span style="display:flex;align-items:center;gap:3px;"><i class="fas fa-circle" style="font-size:6px;color:#06d6a0;"></i> Assessment</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upcoming Tasks --}}
            <div class="sidebar-widget">
                <div class="sidebar-widget-header">
                    <h3><i class="fas fa-tasks"></i> Upcoming</h3>
                </div>
                <div class="sidebar-widget-body">
                    <div class="upcoming-tasks-list" id="upcomingTasks">
                        {{-- Tasks will be loaded dynamically --}}
                        <div class="no-tasks">
                            <i class="fas fa-check-circle"></i>
                            <p>All caught up!</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Categories --}}
            @if($categories->count() > 0)
            <div class="sidebar-widget">
                <div class="sidebar-widget-header">
                    <h3><i class="fas fa-folder"></i> Categories</h3>
                </div>
                <div class="sidebar-widget-body">
                    <div class="category-stats">
                        @foreach($categories as $category)
                        @php
                        $courseCount = $courses->where('category_id', $category->id)->count();
                        @endphp
                        @if($courseCount > 0)
                        <div class="category-stat-item" data-category="{{ $category->id }}">
                            <span class="category-dot" style="background: {{ $category->color }}"></span>
                            <span class="category-name">{{ $category->name }}</span>
                            <span class="category-count">{{ $courseCount }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const gridViewBtn = document.getElementById('gridViewBtn');
        const listViewBtn = document.getElementById('listViewBtn');
        const coursesContainer = document.getElementById('coursesContainer');
        const courseSearch = document.getElementById('courseSearch');
        const noResults = document.getElementById('noResults');
        const categoryStatItems = document.querySelectorAll('.category-stat-item');
        const categoryTabs = document.querySelectorAll('.category-tab');

        let activeCategory = 'all';

        // View Toggle
        function setView(view) {
            if (!coursesContainer) return;

            if (view === 'grid') {
                coursesContainer.classList.remove('list-view');
                coursesContainer.classList.add('grid-view');
                gridViewBtn.classList.add('active');
                listViewBtn.classList.remove('active');
            } else {
                coursesContainer.classList.remove('grid-view');
                coursesContainer.classList.add('list-view');
                listViewBtn.classList.add('active');
                gridViewBtn.classList.remove('active');
            }
            localStorage.setItem('coursesViewPreference', view);
        }

        const savedView = localStorage.getItem('coursesViewPreference') || 'grid';
        setView(savedView);

        if (gridViewBtn) gridViewBtn.addEventListener('click', () => setView('grid'));
        if (listViewBtn) listViewBtn.addEventListener('click', () => setView('list'));

        // Filter and Search
        function filterCourses() {
            const searchTerm = courseSearch ? courseSearch.value.toLowerCase().trim() : '';
            const courseCards = document.querySelectorAll('.course-card');
            let visibleCount = 0;

            courseCards.forEach(function(card) {
                const name = card.dataset.courseName || '';
                const code = card.dataset.courseCode || '';
                const category = card.dataset.category || '';
                const text = card.textContent.toLowerCase();

                const matchesSearch = !searchTerm ||
                    name.includes(searchTerm) ||
                    code.includes(searchTerm) ||
                    text.includes(searchTerm);

                const matchesCategory = activeCategory === 'all' || category === activeCategory;

                if (matchesSearch && matchesCategory) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
            if (coursesContainer) {
                coursesContainer.style.display = visibleCount === 0 ? 'none' : '';
            }
        }

        if (courseSearch) {
            courseSearch.addEventListener('input', filterCourses);
        }

        // Category tabs (toolbar)
        categoryTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const categoryId = this.dataset.category;
                activeCategory = categoryId;

                // Update active state on tabs
                categoryTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Also update sidebar items
                categoryStatItems.forEach(i => i.classList.remove('active'));
                if (categoryId !== 'all') {
                    const sidebarItem = document.querySelector(`.category-stat-item[data-category="${categoryId}"]`);
                    if (sidebarItem) sidebarItem.classList.add('active');
                }

                filterCourses();
            });
        });

        // Category stat items (sidebar)
        categoryStatItems.forEach(item => {
            item.addEventListener('click', function() {
                const categoryId = this.dataset.category;

                // Toggle: if clicking the same category, show all
                if (activeCategory === categoryId) {
                    activeCategory = 'all';
                    categoryStatItems.forEach(i => i.classList.remove('active'));
                    categoryTabs.forEach(t => {
                        t.classList.toggle('active', t.dataset.category === 'all');
                    });
                } else {
                    activeCategory = categoryId;
                    categoryStatItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    categoryTabs.forEach(t => {
                        t.classList.toggle('active', t.dataset.category === categoryId);
                    });
                }

                filterCourses();
            });
        });

        // Mini Calendar with Events
        const calendarGrid = document.getElementById('calendarGrid');
        const calendarMonth = document.getElementById('calendarMonth');
        const prevMonthBtn = document.getElementById('prevMonth');
        const nextMonthBtn = document.getElementById('nextMonth');

        // Calendar events from server
        const calendarEvents = @json($calendarEvents ?? []);

        let currentDate = new Date();

        // Group events by date
        function getEventsForDate(dateStr) {
            return calendarEvents.filter(event => event.date === dateStr);
        }

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            calendarMonth.textContent = currentDate.toLocaleDateString('en-US', {
                month: 'long',
                year: 'numeric'
            });

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDay = firstDay.getDay();
            const daysInMonth = lastDay.getDate();

            const today = new Date();
            const isCurrentMonth = today.getMonth() === month && today.getFullYear() === year;

            let html = '';

            // Day labels
            const days = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
            days.forEach(day => {
                html += `<div class="calendar-day-label">${day}</div>`;
            });

            // Previous month days
            const prevMonthDays = new Date(year, month, 0).getDate();
            for (let i = startDay - 1; i >= 0; i--) {
                html += `<div class="calendar-day other-month">${prevMonthDays - i}</div>`;
            }

            // Current month days
            for (let day = 1; day <= daysInMonth; day++) {
                const isToday = isCurrentMonth && day === today.getDate();
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dayEvents = getEventsForDate(dateStr);
                const hasEvents = dayEvents.length > 0;

                // Generate event dots (max 3 visible)
                let dotsHtml = '';
                if (hasEvents) {
                    const uniqueColors = [...new Set(dayEvents.map(e => e.color))];
                    const visibleColors = uniqueColors.slice(0, 3);
                    dotsHtml = '<div class="calendar-event-dots">';
                    visibleColors.forEach(color => {
                        dotsHtml += `<span class="event-dot" style="background: ${color}"></span>`;
                    });
                    dotsHtml += '</div>';
                }

                // Generate tooltip with event type labels
                const typeLabels = {
                    course_start: 'Course',
                    course_end: 'Course',
                    self_check: 'Self-Check',
                    homework: 'Homework',
                    competency_test: 'Test',
                    document_assessment: 'Assessment'
                };
                let tooltipAttr = '';
                if (hasEvents) {
                    const eventTitles = dayEvents.map(e => `[${typeLabels[e.type] || e.type}] ${e.title}`).join('\\n');
                    tooltipAttr = ` title="${eventTitles}" data-events='${JSON.stringify(dayEvents)}'`;
                }

                html += `<div class="calendar-day${isToday ? ' today' : ''}${hasEvents ? ' has-events' : ''}"${tooltipAttr}>${day}${dotsHtml}</div>`;
            }

            // Next month days
            const remaining = 42 - (startDay + daysInMonth);
            for (let i = 1; i <= remaining; i++) {
                html += `<div class="calendar-day other-month">${i}</div>`;
            }

            calendarGrid.innerHTML = html;
        }

        if (calendarGrid) {
            renderCalendar();

            prevMonthBtn.addEventListener('click', function() {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar();
            });

            nextMonthBtn.addEventListener('click', function() {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar();
            });
        }
    });
</script>
@endpush