// public/js/dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    var userRole = (document.body.getAttribute('data-user-role') || '').toLowerCase();

    if (userRole !== 'admin' && userRole !== 'instructor') {
        loadStudentDashboard();
    }

    initializeProgressCircles();
    initializeActivityFeed();
    initializePendingSorting();
});

async function loadStudentDashboard() {
    try {
        const response = await fetch('/student/dashboard-data', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        updateStudentDashboard(data);
    } catch (error) {
        console.error('Error loading student dashboard:', error);
        // Fallback to server-side rendered data or mock data
        const serverData = {
            progress: document.getElementById('student-progress-text')?.getAttribute('data-progress') || 0,
            finished_activities: document.getElementById('finished-activities')?.getAttribute('data-activities') || '0/0',
            total_modules: document.getElementById('total-modules-count')?.getAttribute('data-modules') || 0,
            average_grade: document.getElementById('average-grade')?.getAttribute('data-grade') || '0%'
        };
        updateStudentDashboard(serverData);
    }
}

function updateStudentDashboard(data) {
    // Update progress circle
    const progressText = document.getElementById('student-progress-text');
    if (progressText) {
        progressText.textContent = data.progress + '%';
        
        // Update progress circle visual (if you have CSS for this)
        const progressCircle = progressText.closest('.progress-circle');
        if (progressCircle) {
            progressCircle.style.setProperty('--progress', data.progress + '%');
        }
    }

    // Update finished activities
    const finishedActivities = document.getElementById('finished-activities');
    if (finishedActivities) {
        finishedActivities.textContent = data.finished_activities;
    }

    // Update total modules
    const totalModulesCount = document.getElementById('total-modules-count');
    if (totalModulesCount) {
        totalModulesCount.textContent = data.total_modules;
    }

    // Update average grade
    const averageGrade = document.getElementById('average-grade');
    if (averageGrade) {
        // Handle both number and string formats
        let gradeValue = data.average_grade;
        if (typeof gradeValue === 'number') {
            averageGrade.textContent = gradeValue.toFixed(1) + '%';
        } else {
            averageGrade.textContent = gradeValue.toString().includes('%') ? gradeValue : gradeValue + '%';
        }
    }
}

// Progress circle animation
function initializeProgressCircles() {
    var progressElements = document.querySelectorAll('.progress-text');
    progressElements.forEach(function(element) {
        var progress = parseInt(element.textContent) || 0;
        var circle = element.closest('.progress-circle');
        if (circle) {
            circle.style.setProperty('--progress', progress + '%');
        }
    });
}

// Announcements filtering and sorting
function initializeActivityFeed() {
    var searchInput = document.getElementById('feed-search');
    var filterType = document.getElementById('feed-filter-type');
    var sortSelect = document.getElementById('feed-sort');
    var noResults = document.getElementById('no-results');
    var activityFeed = document.getElementById('activity-feed');

    if (!activityFeed) return;

    function filterAndSort() {
        // Get fresh list of items each time
        var feedItems = activityFeed.querySelectorAll('.feed-item');
        var searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        var typeFilter = filterType ? filterType.value : '';
        var sortOrder = sortSelect ? sortSelect.value : 'newest';

        var visibleCount = 0;
        var items = Array.from(feedItems);

        // First, sort ALL items by date
        items.sort(function(a, b) {
            var dateA = new Date(a.dataset.date || 0);
            var dateB = new Date(b.dataset.date || 0);
            return sortOrder === 'newest' ? dateB - dateA : dateA - dateB;
        });

        // Then apply filters and re-append in sorted order
        items.forEach(function(item) {
            var type = item.dataset.type;
            var subtype = item.dataset.subtype || '';
            var module = item.dataset.module || '';
            var user = item.dataset.user || '';
            var text = item.textContent.toLowerCase();

            var matchesSearch = !searchTerm ||
                text.includes(searchTerm) ||
                module.includes(searchTerm) ||
                user.includes(searchTerm);

            var matchesType = !typeFilter ||
                type === typeFilter ||
                subtype === typeFilter ||
                (typeFilter === 'quiz' && subtype === 'self_check') ||
                (typeFilter === 'task' && subtype === 'task_sheet');

            if (matchesSearch && matchesType) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }

            // Re-append item in sorted order
            activityFeed.appendChild(item);
        });

        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (searchInput) searchInput.addEventListener('input', filterAndSort);
    if (filterType) filterType.addEventListener('change', filterAndSort);
    if (sortSelect) sortSelect.addEventListener('change', filterAndSort);
}

// Pending list sorting
function initializePendingSorting() {
    document.querySelectorAll('.pending-sort').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var sortType = this.dataset.sort;
            var list = document.getElementById('pending-list');
            if (!list) return;

            var items = Array.from(list.querySelectorAll('.pending-item'));

            items.sort(function(a, b) {
                if (sortType === 'date-desc') {
                    return new Date(b.dataset.date || 0) - new Date(a.dataset.date || 0);
                } else if (sortType === 'date-asc') {
                    return new Date(a.dataset.date || 0) - new Date(b.dataset.date || 0);
                } else if (sortType === 'type') {
                    return (a.dataset.type || '').localeCompare(b.dataset.type || '');
                }
                return 0;
            });

            items.forEach(function(item) { list.appendChild(item); });
        });
    });
}
