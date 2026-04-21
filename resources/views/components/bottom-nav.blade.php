<!-- Bottom Navigation for Mobile -->
<nav class="bottom-nav" id="bottomNav">
    <a href="{{ dynamic_route('dashboard') }}" class="nav-link {{ Request::routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-home"></i>
        <span class="nav-label">Home</span>
    </a>

    <a href="{{ dynamic_route('grades.index') }}" class="nav-link {{ Request::is('grades*') ? 'active' : '' }}">
        <i class="fas fa-graduation-cap"></i>
        <span class="nav-label">Grades</span>
    </a>

    <a href="{{ dynamic_route('courses.index') }}" class="nav-link nav-link-center {{ Request::is('courses*') || Request::is('modules*') ? 'active' : '' }}">
        <span class="center-btn">
            <i class="fas fa-book-open"></i>
        </span>
        <span class="nav-label">Courses</span>
    </a>

    <a href="{{ route('private.announcements.index') }}" class="nav-link {{ Request::is('announcements*') ? 'active' : '' }}">
        <i class="fas fa-bullhorn"></i>
        <span class="nav-label">News</span>
    </a>

    <a href="{{ route('settings.index') }}" class="nav-link {{ Request::routeIs('settings.*') ? 'active' : '' }}">
        <i class="fas fa-cog"></i>
        <span class="nav-label">Settings</span>
    </a>
</nav>

<style>
    /* Bottom nav hidden by default, shown only on mobile (1032px matches mobile.css) */
    .bottom-nav {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 68px;
        background: var(--bottom-nav-bg, #ffffff);
        border-top: 1px solid var(--bottom-nav-border, #e5e7eb);
        justify-content: space-around;
        align-items: center;
        z-index: var(--z-navbar); /* 100 — below sidebar/backdrop */
        padding: 6px 0.5rem 10px;
        box-shadow: 0 -2px 10px var(--bottom-nav-shadow, rgba(0,0,0,0.05));
    }

    @media (max-width: 1032px) {
        .bottom-nav {
            display: flex;
        }

        body {
            padding-bottom: 78px !important;
        }
    }

    .bottom-nav .nav-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--bottom-nav-text, #6b7280);
        text-decoration: none;
        padding: 0.5rem;
        border-radius: 8px;
        transition: all 0.2s;
        flex: 1;
        max-width: 80px;
        position: relative;
        background: none;
        border: none;
    }

    .bottom-nav .nav-link:hover,
    .bottom-nav .nav-link:focus {
        color: var(--primary, #0c3a2d);
        background: rgba(12, 58, 45, 0.05);
    }

    .bottom-nav .nav-link.active {
        color: var(--primary, #0c3a2d);
    }

    .bottom-nav .nav-link.active::after {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 20px;
        height: 3px;
        background: var(--primary, #0c3a2d);
        border-radius: 0 0 3px 3px;
    }

    .bottom-nav .nav-link i {
        font-size: 1.25rem;
        margin-bottom: 0.25rem;
        transition: transform 0.2s ease;
    }

    .bottom-nav .nav-link.active i {
        transform: scale(1.1);
    }

    .bottom-nav .nav-label {
        font-size: 0.625rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        transition: color 0.2s ease;
    }

    /* ── Center Courses FAB (behind the card) ── */
    .bottom-nav .nav-link-center {
        position: relative;
        max-width: 90px;
        z-index: -1;
    }

    .bottom-nav .nav-link-center .center-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--primary, #0c3a2d);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(12, 58, 45, 0.4);
        margin-top: -38px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .bottom-nav .nav-link-center .center-btn i {
        font-size: 1.5rem;
        color: #ffffff;
        margin-bottom: 0;
    }

    .bottom-nav .nav-link-center:hover .center-btn,
    .bottom-nav .nav-link-center:focus .center-btn {
        transform: scale(1.08);
        box-shadow: 0 6px 18px rgba(12, 58, 45, 0.5);
    }

    .bottom-nav .nav-link-center:active .center-btn {
        transform: scale(0.95);
    }

    .bottom-nav .nav-link-center .nav-label {
        margin-top: 2px;
    }

    /* Override active indicator for center button */
    .bottom-nav .nav-link-center.active::after {
        display: none;
    }

    .bottom-nav .nav-link-center.active .center-btn {
        box-shadow: 0 4px 18px rgba(12, 58, 45, 0.55);
    }

    /* Override hover background for center button */
    .bottom-nav .nav-link-center:hover,
    .bottom-nav .nav-link-center:focus {
        background: none;
    }

    /* Active press effect */
    .bottom-nav .nav-link:active {
        transform: scale(0.92);
    }

    .bottom-nav .nav-link-center:active {
        transform: none; /* handled by .center-btn */
    }

    /* Safe area padding for notched devices */
    @supports (padding: env(safe-area-inset-bottom)) {
        .bottom-nav {
            padding-bottom: env(safe-area-inset-bottom);
            height: calc(60px + env(safe-area-inset-bottom));
        }

        @media (max-width: 1032px) {
            body {
                padding-bottom: calc(70px + env(safe-area-inset-bottom)) !important;
            }
        }
    }

    /* Dark mode support */
    .dark-mode {
        --bottom-nav-bg: #1e293b;
        --bottom-nav-border: #334155;
        --bottom-nav-shadow: rgba(0,0,0,0.3);
        --bottom-nav-text: #94a3b8;
    }

    .dark-mode .bottom-nav .nav-link-center .center-btn {
        box-shadow: 0 4px 12px rgba(12, 58, 45, 0.5);
    }

    /* Hide bottom nav when keyboard is open on mobile */
    @media (max-height: 500px) {
        .bottom-nav {
            display: none !important;
        }
    }

    /* Landscape adjustments */
    @media (max-width: 1032px) and (orientation: landscape) {
        .bottom-nav {
            height: 48px;
        }

        .bottom-nav .nav-link i {
            font-size: 1.1rem;
            margin-bottom: 0.125rem;
        }

        .bottom-nav .nav-label {
            font-size: 0.5625rem;
        }

        .bottom-nav .nav-link-center .center-btn {
            width: 44px;
            height: 44px;
            margin-top: -20px;
        }

        .bottom-nav .nav-link-center .center-btn i {
            font-size: 1.2rem;
        }
    }
</style>
