document.addEventListener('DOMContentLoaded', function() {
    // Function to handle image preview
    function handleImagePreview(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Avatar upload in sidebar
    const avatarUpload = document.getElementById('avatar-upload');
    const avatarForm = document.getElementById('avatar-form');
    
    if (avatarUpload && avatarForm) {
        avatarUpload.addEventListener('change', function() {
            handleImagePreview(this, 'avatar-preview');
            avatarForm.submit();
        });
    }

    // Sidebar toggle functionality — uses event delegation so it survives Livewire SPA navigation
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    // Sidebar toggle logic:
    // - Click profile area at top of sidebar = toggle collapse/expand
    // - Click outside expanded sidebar = collapse
    function collapseSidebar() {
        sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
        document.documentElement.classList.add('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', 'true');
    }
    function expandSidebar() {
        sidebar.classList.remove('collapsed');
        document.body.classList.remove('sidebar-collapsed');
        document.documentElement.classList.remove('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', 'false');
    }

    document.addEventListener('click', function(e) {
        if (!sidebar) return;
        var clickedSidebar = e.target.closest('.sidebar');
        var clickedNavLink = e.target.closest('a.nav-item');
        var clickedFlyout = e.target.closest('.flyout-menu');
        var clickedFlyoutTrigger = e.target.closest('.nav-item.has-flyout');
        var clickedProfile = e.target.closest('.sidebar-profile');

        // Never collapse/expand when interacting with flyout
        if (clickedFlyout || clickedFlyoutTrigger) return;

        if (sidebar.classList.contains('collapsed')) {
            // COLLAPSED: click anywhere on sidebar (except nav links) = expand
            if (clickedSidebar && !clickedNavLink) {
                expandSidebar();
                return;
            }
        } else {
            // EXPANDED: click profile = collapse
            if (clickedProfile) {
                collapseSidebar();
                return;
            }
            // EXPANDED: click outside sidebar = collapse (but not flyout menus)
            if (!clickedSidebar && !e.target.closest('.popover') && !e.target.closest('.dropdown') && !e.target.closest('.fab-container') && !e.target.closest('.flyout-menu')) {
                collapseSidebar();
                return;
            }
        }
    });

    // Event delegation: clicking sidebar-toggle or mobile hamburger
    document.addEventListener('click', function(e) {
        var toggleBtn = e.target.closest('#sidebar-toggle, #hamburger-menu');
        var mobileToggle = e.target.closest('#mobile-sidebar-toggle');

        // Mobile hamburger toggle
        if (mobileToggle && sidebar) {
            sidebar.classList.toggle('sidebar-open');
            if (overlay) overlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
            return;
        }

        // Desktop toggle
        if (toggleBtn && sidebar) {
            sidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed');
            var icon = toggleBtn.querySelector('i');
            if (icon) {
                icon.className = sidebar.classList.contains('collapsed')
                    ? 'fa-solid fa-chevron-right'
                    : 'fa-solid fa-chevron-left';
            }
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }
    });

    // Apply saved state on load — default to collapsed if no preference saved
    if (sidebar) {
        const savedState = localStorage.getItem('sidebarCollapsed');
        const shouldExpand = savedState === 'false'; // only expand if user explicitly chose to

        if (shouldExpand) {
            sidebar.classList.remove('collapsed');
            document.body.classList.remove('sidebar-collapsed');
            document.documentElement.classList.remove('sidebar-collapsed');
            const icon = document.querySelector('#sidebar-toggle i, #hamburger-menu i');
            if (icon) icon.className = 'fa-solid fa-chevron-left';
        } else {
            // Ensure collapsed state is applied
            sidebar.classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
            document.documentElement.classList.add('sidebar-collapsed');
            const icon = document.querySelector('#sidebar-toggle i, #hamburger-menu i');
            if (icon) icon.className = 'fa-solid fa-chevron-right';
        }
    }

    // Flyout menu toggle — click nav items with .has-flyout to open/close
    document.addEventListener('click', function(e) {
        var flyoutParent = e.target.closest('.nav-item-flyout');
        var clickedFlyoutTrigger = e.target.closest('.nav-item.has-flyout');

        // Close all other flyouts first
        document.querySelectorAll('.nav-item-flyout.open').forEach(function(el) {
            if (el !== flyoutParent) el.classList.remove('open');
        });

        // Toggle clicked flyout and position it
        if (clickedFlyoutTrigger && flyoutParent) {
            e.preventDefault();
            var wasOpen = flyoutParent.classList.contains('open');
            flyoutParent.classList.toggle('open');

            // Position the flyout menu next to the trigger using fixed positioning
            if (!wasOpen) {
                var flyout = flyoutParent.querySelector('.flyout-menu');
                if (flyout) {
                    var rect = clickedFlyoutTrigger.getBoundingClientRect();
                    var sidebarEl = document.getElementById('sidebar');
                    // Get the actual right edge of the sidebar including its position
                    var sidebarRect = sidebarEl ? sidebarEl.getBoundingClientRect() : null;
                    var leftPos = sidebarRect ? (sidebarRect.left + sidebarRect.width + 16) : (rect.right + 16);
                    flyout.style.left = leftPos + 'px';
                    flyout.style.top = Math.max(rect.top, 10) + 'px';
                    // Prevent flyout from going off screen bottom
                    requestAnimationFrame(function() {
                        var flyoutRect = flyout.getBoundingClientRect();
                        if (flyoutRect.bottom > window.innerHeight - 10) {
                            flyout.style.top = (window.innerHeight - flyoutRect.height - 10) + 'px';
                        }
                    });
                }
            }
            return;
        }

        // Click inside flyout menu — don't close (let links work)
        if (e.target.closest('.flyout-menu')) return;

        // Click anywhere else — close all flyouts
        document.querySelectorAll('.nav-item-flyout.open').forEach(function(el) {
            el.classList.remove('open');
        });
    });

    // Mobile sidebar functionality
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('sidebar-open');
            overlay.classList.remove('active');
            if (hamburgerMenu) {
                hamburgerMenu.classList.remove('active');
            }
            document.body.classList.remove('sidebar-open');
        });
    }

    // Close sidebar when clicking on a link (mobile only)
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Only close on mobile (screen width less than 1032px)
            if (window.innerWidth < 1032) {
                sidebar.classList.remove('sidebar-open');
                if (overlay) {
                    overlay.classList.remove('active');
                }
                if (hamburgerMenu) {
                    hamburgerMenu.classList.remove('active');
                }
                document.body.classList.remove('sidebar-open');
            }
        });
    });

    // Close sidebar when pressing Escape key (mobile only)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && window.innerWidth < 1032) {
            sidebar.classList.remove('sidebar-open');
            if (overlay) {
                overlay.classList.remove('active');
            }
            if (hamburgerMenu) {
                hamburgerMenu.classList.remove('active');
            }
            document.body.classList.remove('sidebar-open');
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        // On desktop, ensure sidebar is visible (not in mobile slide-out mode)
        if (window.innerWidth >= 1032 && sidebar) {
            sidebar.classList.remove('sidebar-open');
            if (overlay) {
                overlay.classList.remove('active');
            }
            document.body.classList.remove('sidebar-open');
        }
    });
});

// Search bar expand functionality
const searchNav = document.getElementById('search-nav');
if (searchNav) {
    searchNav.addEventListener('click', function(e) {
        // Don't toggle if clicking on the input
        if (e.target.classList.contains('search-input')) return;
        
        this.classList.toggle('expanded');
        
        // Focus input when expanded
        if (this.classList.contains('expanded')) {
            const input = this.querySelector('.search-input');
            setTimeout(() => input.focus(), 300);
        }
    });

    // Close search when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchNav.contains(e.target) && searchNav.classList.contains('expanded')) {
            searchNav.classList.remove('expanded');
        }
    });

    // Close search on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && searchNav.classList.contains('expanded')) {
            searchNav.classList.remove('expanded');
        }
    });
}

// ===== SWIPE GESTURES (mobile) =====
(function() {
    var touchStartX = 0, touchStartY = 0, touchEndX = 0, touchEndY = 0;
    var minSwipe = 60;

    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        touchEndY = e.changedTouches[0].screenY;

        var dx = touchEndX - touchStartX;
        var dy = touchEndY - touchStartY;
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('overlay');

        // Only on mobile
        if (window.innerWidth >= 1032) return;

        // Swipe right → open sidebar (from left edge, first 40px)
        if (dx > minSwipe && Math.abs(dy) < 80 && touchStartX < 40) {
            if (sidebar) {
                sidebar.classList.add('sidebar-open');
                if (overlay) overlay.classList.add('active');
                document.body.classList.add('sidebar-open');
            }
            return;
        }

        // Swipe left → close sidebar (if open)
        if (dx < -minSwipe && Math.abs(dy) < 80) {
            if (sidebar && sidebar.classList.contains('sidebar-open')) {
                sidebar.classList.remove('sidebar-open');
                if (overlay) overlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            }
            return;
        }

        // Swipe down → close active popover/dropdown
        if (dy > minSwipe && Math.abs(dx) < 80) {
            document.querySelectorAll('.popover.active, .dropdown.active').forEach(function(el) {
                el.classList.remove('active');
            });
        }
    }, { passive: true });
})();

