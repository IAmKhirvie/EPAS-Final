class TopNavbar {
    constructor() {
        this.currentUser = null;
        this.activePopover = null;
        
        this.init();
    }

    async init() {
        await this.loadUserData();
        this.setupEventListeners();
        this.setupTooltips();
        // Dark mode is handled by utils/dark-mode.js (loaded before this file)
        if (window.initDarkMode) window.initDarkMode();
    }

    async loadUserData() {
        try {
            // In a real app, this would be an API call
            const avatarElement = document.getElementById('navbar-avatar');
            this.currentUser = {
                firstName: '{{ Auth::user()->first_name }}',
                lastName: '{{ Auth::user()->last_name }}',
                role: '{{ Auth::user()->role }}',
                avatar: avatarElement ? avatarElement.src : '/images/default-avatar.png'
            };
        } catch (error) {
            console.error('Failed to load user data:', error);
            // Set default user data if loading fails
            this.currentUser = {
                firstName: 'User',
                lastName: '',
                role: 'guest',
                avatar: '/images/default-avatar.png'
            };
        }
    }

    setupTooltips() {
        // ... keep existing tooltip code ...
    }

    setupEventListeners() {
        // Sidebar toggle (desktop)
        const sidebarToggle = document.getElementById('sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                this.toggleSidebar();
            });
        }

        // Mobile sidebar toggle (hamburger in navbar-left-2)
        const mobileSidebarToggle = document.getElementById('mobile-sidebar-toggle');
        if (mobileSidebarToggle) {
            mobileSidebarToggle.addEventListener('click', () => {
                this.toggleSidebar();
            });
        }

        // Notifications
        const notificationsBtn = document.getElementById('notifications-btn');
        const notificationsPopover = document.getElementById('notifications-popover');

        if (notificationsBtn && notificationsPopover) {
            notificationsBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.togglePopover('notifications-popover');
            });
        }

        // User menu (desktop + mobile buttons both open the same dropdown)
        const userMenuBtn = document.getElementById('user-menu-btn');
        const userMenuBtnMobile = document.getElementById('user-menu-btn-mobile');
        const userDropdown = document.getElementById('user-dropdown');

        if (userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.togglePopover('user-dropdown');
            });
        }

        if (userMenuBtnMobile && userDropdown) {
            userMenuBtnMobile.addEventListener('click', (e) => {
                e.stopPropagation();
                this.togglePopover('user-dropdown');
            });
        }

        // Logout
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleLogout();
            });
        }

        // Close popovers when clicking outside
        document.addEventListener('click', () => {
            this.closeAllPopovers();
        });

        // Prevent popover close when clicking inside
        document.querySelectorAll('.popover, .dropdown').forEach(element => {
            element.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

        // Add click event to document to close sidebar when clicking outside on mobile
        document.addEventListener('click', this.handleOutsideClick.bind(this));
    }

    // Handle clicks outside the sidebar on mobile
    handleOutsideClick(event) {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const mobileSidebarToggle = document.getElementById('mobile-sidebar-toggle');

        // If we're on mobile and sidebar is visible
        if (window.innerWidth < 1032 &&
            sidebar &&
            !sidebar.classList.contains('mobile-hidden') &&
            !sidebar.contains(event.target) &&
            (!sidebarToggle || !sidebarToggle.contains(event.target)) &&
            (!mobileSidebarToggle || !mobileSidebarToggle.contains(event.target))) {

            this.hideSidebar();
        }
    }

    // Mobile sidebar toggle behavior
    toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const body = document.body;
        
        if (sidebar) {
            if (window.innerWidth < 1032) {
                const isHidden = sidebar.classList.contains('mobile-hidden');
                
                if (isHidden) {
                    // Show sidebar
                    this.showSidebar();
                } else {
                    // Hide sidebar
                    this.hideSidebar();
                }
            } else {
                // Desktop behavior
                sidebar.classList.toggle('collapsed');
                body.classList.toggle('sidebar-collapsed');
            }
        }
    }

    showSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.remove('mobile-hidden');
            this.showBackdrop();
        }
    }

    hideSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.add('mobile-hidden');
            this.hideBackdrop();
        }
    }

    showBackdrop() {
        let backdrop = document.getElementById('sidebar-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'sidebar-backdrop';
            backdrop.className = 'sidebar-backdrop';
            document.body.appendChild(backdrop);
        }

        // Always attach click handler (covers both pre-existing and newly created elements)
        backdrop.onclick = () => {
            this.hideSidebar();
        };

        setTimeout(() => {
            backdrop.classList.add('active');
        }, 10);
    }

    hideBackdrop() {
        const backdrop = document.getElementById('sidebar-backdrop');
        if (backdrop) {
            backdrop.classList.remove('active');
            setTimeout(() => {
                if (backdrop.parentNode) {
                    backdrop.parentNode.removeChild(backdrop);
                }
            }, 300);
        }
    }

    togglePopover(popoverId) {
        const popover = document.getElementById(popoverId);

        if (this.activePopover === popoverId) {
            this.closeAllPopovers();
            return;
        }

        this.closeAllPopovers();

        if (popover) {
            // On mobile, move popover to <body> so it escapes navbar stacking context
            if (window.innerWidth <= 1032) {
                if (!popover._originalParent) {
                    popover._originalParent = popover.parentElement;
                }
                document.body.appendChild(popover);
                this.showSheetBackdrop();
            }
            popover.classList.add('active');
            this.activePopover = popoverId;
        }
    }

    closeAllPopovers() {
        document.querySelectorAll('.popover, .dropdown').forEach(element => {
            element.classList.remove('active');
            // Move back to original parent
            if (element._originalParent && element.parentElement === document.body) {
                element._originalParent.appendChild(element);
            }
        });
        this.activePopover = null;
        this.hideSheetBackdrop();
    }

    showSheetBackdrop() {
        let backdrop = document.getElementById('sheet-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'sheet-backdrop';
            backdrop.className = 'sheet-backdrop';
            backdrop.addEventListener('click', () => this.closeAllPopovers());
            document.body.appendChild(backdrop);
        }
        requestAnimationFrame(() => backdrop.classList.add('active'));
    }

    hideSheetBackdrop() {
        const backdrop = document.getElementById('sheet-backdrop');
        if (backdrop) {
            backdrop.classList.remove('active');
            setTimeout(() => backdrop.remove(), 350);
        }
    }

    handleLogout() {
        document.getElementById('logout-form').submit();
    }
}

// Global functions (needed for onclick handlers)
function markAsRead(event, announcementId)
{
    // Simply navigate to the announcement - read tracking removed
    window.location.href = event.currentTarget.href;
}

function dismissNotification(button, announcementId) {
    event.preventDefault();
    event.stopPropagation();

    // Get dismissed notifications from localStorage
    var dismissed = JSON.parse(localStorage.getItem('dismissedNotifications') || '[]');

    // Add this notification to dismissed list
    if (!dismissed.includes(announcementId)) {
        dismissed.push(announcementId);
        localStorage.setItem('dismissedNotifications', JSON.stringify(dismissed));
    }

    // Remove the notification item from DOM
    var item = button.closest('.notification-item');
    if (item) {
        item.style.transition = 'opacity 0.3s, transform 0.3s';
        item.style.opacity = '0';
        item.style.transform = 'translateX(20px)';

        setTimeout(function() {
            item.remove();

            // Update badge count
            var badge = document.getElementById('notification-badge');
            if (badge) {
                var count = parseInt(badge.textContent) - 1;
                if (count > 0) {
                    badge.textContent = count;
                } else {
                    badge.style.display = 'none';
                }
            }

            // Check if no more notifications
            var list = document.getElementById('notifications-list');
            if (list && list.querySelectorAll('.notification-item:not(.empty)').length === 0) {
                list.innerHTML = '<div class="notification-item empty">' +
                    '<div class="notification-content text-center py-4">' +
                    '<div class="empty-icon"><i class="fas fa-inbox" aria-hidden="true"></i></div>' +
                    '<div class="empty-text">No announcements yet</div>' +
                    '<div class="empty-subtext">Check back later for updates</div>' +
                    '</div></div>';
            }
        }, 300);
    }
}

// Hide dismissed notifications on page load
function hideDismissedNotifications() {
    var dismissed = JSON.parse(localStorage.getItem('dismissedNotifications') || '[]');
    var hiddenCount = 0;

    dismissed.forEach(function(id) {
        var item = document.querySelector('.notification-item[data-announcement-id="' + id + '"]');
        if (item) {
            item.remove();
            hiddenCount++;
        }
    });

    // Update badge count
    if (hiddenCount > 0) {
        var badge = document.getElementById('notification-badge');
        if (badge) {
            var count = parseInt(badge.textContent) - hiddenCount;
            if (count > 0) {
                badge.textContent = count;
            } else {
                badge.style.display = 'none';
            }
        }
    }
}

function updateNotificationBadge() {
    const badge = document.getElementById('notification-badge');
    if (badge) {
        const currentCount = parseInt(badge.textContent);
        if (currentCount > 1) {
            badge.textContent = currentCount - 1;
        } else {
            badge.remove();
        }
    }
}

// Function to update notification count
// Simplified since we no longer track read/unread status
function updateNotificationCount() {
    // Remove the notification badge since read tracking is disabled
    const badge = document.getElementById('notification-badge');
    if (badge) {
        badge.remove();
    }
}

function sortNotifications(sortBy) {
    const notificationsList = document.getElementById('notifications-list');
    const notificationItems = Array.from(notificationsList.querySelectorAll('.notification-item:not(.empty)'));

    notificationItems.sort((a, b) => {
        const deadlineA = a.getAttribute('data-deadline') || Number.MAX_SAFE_INTEGER;
        const deadlineB = b.getAttribute('data-deadline') || Number.MAX_SAFE_INTEGER;

        switch(sortBy) {
            case 'deadline':
                return parseInt(deadlineA) - parseInt(deadlineB);

            case 'unread':
                // Read tracking removed - all items considered "read", sort by date instead
                return parseInt(b.getAttribute('data-created-at')) - parseInt(a.getAttribute('data-created-at'));

            case 'newest':
            default:
                return parseInt(b.getAttribute('data-created-at')) - parseInt(a.getAttribute('data-created-at'));
        }
    });

    // Re-append sorted items
    notificationItems.forEach(item => notificationsList.appendChild(item));
}

function handleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;

    if (window.innerWidth < 1032) {
        sidebar.classList.add('mobile-hidden');
        // On mobile, remove collapsed class to ensure full sidebar when opened
        // (collapsed state is desktop-only)
    } else {
        sidebar.classList.remove('mobile-hidden');
        const body = document.body;
        if (sidebar.classList.contains('collapsed')) {
            body.classList.add('sidebar-collapsed');
        } else {
            body.classList.remove('sidebar-collapsed');
        }
    }

    // Hide any open backdrop when switching modes
    const backdrop = document.getElementById('sidebar-backdrop');
    if (backdrop && window.innerWidth >= 1032) {
        backdrop.classList.remove('active');
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.topNavbar = new TopNavbar();

    // Hide previously dismissed notifications
    hideDismissedNotifications();

    // Notification sorting
    const sortSelect = document.getElementById('notification-sort');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortNotifications(this.value);
        });
    }

    // Initialize notification count
    updateNotificationCount();

    // Set up periodic updates (every 30 seconds)
    setInterval(updateNotificationCount, 30000);

    // Sidebar toggle rotation  ← ADD THIS
    const toggle = document.getElementById('sidebar-toggle');
    if (toggle) {
        toggle.addEventListener('click', () => {
            toggle.classList.toggle('active');
        });
    }
});

// Handle window resize for mobile menu
window.addEventListener('resize', handleMobileMenu);
handleMobileMenu();