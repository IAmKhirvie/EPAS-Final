/**
 * Public Page Navbar — Authenticated Users
 *
 * Handles notification popover, user dropdown, logout, and
 * bottom-sheet behavior on public pages (lobby, contact, about).
 *
 * Unlike navbar.js (which manages sidebar for the dashboard),
 * this script has NO sidebar / hamburger logic.
 */
document.addEventListener('DOMContentLoaded', function () {
    var activePopover = null;

    // ── Notification bell ──
    var notificationsBtn = document.getElementById('notifications-btn');
    if (notificationsBtn) {
        notificationsBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            togglePopover('notifications-popover');
        });
    }

    // ── User menu (desktop + mobile buttons) ──
    var userMenuBtn = document.getElementById('user-menu-btn');
    var userMenuBtnMobile = document.getElementById('user-menu-btn-mobile');

    if (userMenuBtn) {
        userMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            togglePopover('user-dropdown');
        });
    }

    if (userMenuBtnMobile) {
        userMenuBtnMobile.addEventListener('click', function (e) {
            e.stopPropagation();
            togglePopover('user-dropdown');
        });
    }

    // ── Logout ──
    var logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('logout-form').submit();
        });
    }

    // ── Close all when clicking outside ──
    document.addEventListener('click', function () {
        closeAllPopovers();
    });

    // Prevent close when clicking inside a popover / dropdown
    document.querySelectorAll('.popover, .dropdown').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });

    // ── Escape key ──
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllPopovers();
    });

    // ── Toggle popover (with mobile bottom-sheet) ──
    function togglePopover(popoverId) {
        var popover = document.getElementById(popoverId);
        if (!popover) return;

        if (activePopover === popoverId) {
            closeAllPopovers();
            return;
        }

        closeAllPopovers();

        // On mobile, move popover to body so it escapes navbar stacking context
        if (window.innerWidth <= 1032) {
            if (!popover._originalParent) {
                popover._originalParent = popover.parentElement;
            }
            document.body.appendChild(popover);
            showSheetBackdrop();
        }

        popover.classList.add('active');
        activePopover = popoverId;
    }

    function closeAllPopovers() {
        document.querySelectorAll('.popover.active, .dropdown.active').forEach(function (el) {
            el.classList.remove('active');
            // Return to original parent
            if (el._originalParent && el.parentElement === document.body) {
                el._originalParent.appendChild(el);
            }
        });
        activePopover = null;
        hideSheetBackdrop();
    }

    // ── Sheet backdrop (mobile) ──
    function showSheetBackdrop() {
        var backdrop = document.getElementById('sheet-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'sheet-backdrop';
            backdrop.className = 'sheet-backdrop';
            backdrop.addEventListener('click', function () {
                closeAllPopovers();
            });
            document.body.appendChild(backdrop);
        }
        requestAnimationFrame(function () {
            backdrop.classList.add('active');
        });
    }

    function hideSheetBackdrop() {
        var backdrop = document.getElementById('sheet-backdrop');
        if (backdrop) {
            backdrop.classList.remove('active');
            setTimeout(function () {
                if (backdrop.parentElement) backdrop.remove();
            }, 350);
        }
    }

    // ── Init dark mode ──
    if (window.initDarkMode) window.initDarkMode();
});
