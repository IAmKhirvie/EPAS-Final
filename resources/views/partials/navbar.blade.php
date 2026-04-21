<!-- Top Navbar - Private-->
<header class="top-navbar">

    <!-- Left side - Search bar -->
    <div class="navbar-left">
        <div class="navbar-search">
            <i class="fas fa-search"></i>
            <input type="text" id="global-search" placeholder="Search courses, modules, users..." autocomplete="off">
            <div class="search-results" id="search-results"></div>
        </div>
    </div>

    <!-- Left side - Logo and Title (mobile) -->
    <div class="navbar-left-2">
        <button class="mobile-sidebar-toggle" id="mobile-sidebar-toggle" aria-label="Open menu">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
        <a class="navbar-brand" href="{{ route('welcome') }}">
            <div class="navbar-logo-container">
                <img src="{{ dynamic_asset('assets/EPAS-E.png') }}" alt="EPAS-E LMS" class="logo">
                <div class="navbar-title-container">
                    <h2>EPAS-E</h2>
                    <p>Electronic Products Assembly and Servicing</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Right side actions - Rounded container -->
    <div class="navbar-right">
        <!-- Home Icon -->
        <div class="navbar-item">
            <a class="icon-button" href="{{ route('welcome') }}" aria-label="Home">
                <i class="fa-solid fa-house" aria-hidden="true"></i>
            </a>
        </div>

        <!-- Dark Mode Toggle -->
        <div class="navbar-item">
            <button class="icon-button" id="dark-mode-toggle" aria-label="Toggle dark mode">
                <i class="fas fa-moon" id="dark-mode-icon" aria-hidden="true"></i>
            </button>
        </div>

        <!-- Notifications / Announcements -->
        <div class="navbar-item">
            <button class="icon-button" id="notifications-btn" title="Announcements" aria-label="Announcements">
                <i class="fas fa-bell" aria-hidden="true"></i>
                <span class="notification-badge" id="notification-badge" style="{{ (isset($recentAnnouncementsCount) && $recentAnnouncementsCount > 0) ? '' : 'display:none' }}">
                    {{ $recentAnnouncementsCount ?? 0 }}
                </span>
            </button>
            <div class="popover notifications-popover" id="notifications-popover">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.75rem 1rem;border-bottom:1px solid #f0f0f0;">
                    <strong style="font-size:0.88rem;display:flex;align-items:center;gap:0.4rem;"><i class="fas fa-bell" style="color:var(--primary,#0c3a2d);font-size:0.8rem;"></i> Notifications</strong>
                    <a href="{{ route('private.announcements.index') }}" style="font-size:0.7rem;color:var(--primary);text-decoration:none;font-weight:600;">View All</a>
                </div>
                <div id="notifications-list" style="max-height:350px;overflow-y:auto;scrollbar-width:thin;">
                    @php $notifications = isset($recentAnnouncements) ? $recentAnnouncements : collect(); @endphp
                    @forelse($notifications as $announcement)
                    <a href="{{ route('private.announcements.show', $announcement) }}" class="notif-row" data-id="{{ $announcement->id }}" onclick="markRead(this)">
                        <div class="notif-dot {{ $announcement->is_urgent ? 'urgent' : ($announcement->is_pinned ? 'pinned' : '') }}"></div>
                        <div class="notif-body">
                            <div class="notif-title">{{ Str::limit($announcement->title, 40) }}</div>
                            <div class="notif-sub">{{ Str::limit(strip_tags($announcement->content ?? ''), 60) }}</div>
                            <div class="notif-time">{{ $announcement->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                    @empty
                    <div style="text-align:center;padding:2rem 1rem;color:#ccc;">
                        <i class="fas fa-bell-slash" style="font-size:1.5rem;margin-bottom:0.5rem;display:block;opacity:0.4;"></i>
                        <span style="font-size:0.78rem;">No notifications</span>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- User menu -->
        <div class="navbar-item">
            @php $user = Auth::user(); @endphp
            {{-- Desktop: compact avatar button --}}
            <button class="user-button user-button--desktop" id="user-menu-btn">
                <div class="icon-button nav-avatar-btn" id="desktop-avatar-trigger" title="{{ $user->full_name }}">
                    @if($user->profile_image)
                    <img src="{{ $user->profile_image_url }}" alt="" class="nav-avatar-img">
                    @else
                    <span class="nav-avatar-initials">{{ $user->initials }}</span>
                    @endif
                </div>
            </button>
            {{-- Mobile: ID card strip button --}}
            <button class="user-button user-button--mobile" id="user-menu-btn-mobile">
                <div class="avatar" id="mobile-avatar-trigger" title="Tap to change photo">
                    <img src="{{ $user->profile_image_url }}" alt="User Avatar" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <span class="avatar-fallback" style="display: {{ $user->profile_image ? 'none' : 'flex' }};">{{ $user->initials }}</span>
                </div>
                <div class="user-button__info">
                    <span class="user-button__name">{{ $user->first_name }}</span>
                    <span class="user-button__role">{{ ucfirst($user->role) }}</span>
                </div>
                <i class="fas fa-chevron-down user-button__arrow"></i>
            </button>
            <div class="dropdown" id="user-dropdown">
                <div class="dropdown-content">
                    {{-- Profile Header —  ID card style --}}
                    <div class="user-card-header" id="dropdown-avatar-trigger" title="Tap to change photo"
                        style="cursor: pointer; {{ $user->profile_image ? 'background-image: url(' . $user->profile_image_url . ');' : '' }}">
                        <div class="user-card-header-overlay"></div>
                        @if(!$user->profile_image)
                        <span class="user-card-header-initials">{{ $user->initials }}</span>
                        @endif
                        <div class="user-card-header-info">
                            <h2 class="user-card-name">{{ $user->full_name }}</h2>
                            <h3 class="user-card-subtitle">
                                <span class="user-card-role-badge role-{{ $user->role }}">{{ $user->role_display }}</span>
                                @if($user->student_id)
                                <span class="user-card-id">ID: {{ $user->student_id }}</span>
                                @endif
                            </h3>
                        </div>
                    </div>

                    <div class="dropdown-divider"></div>

                    {{-- User Info Section --}}
                    <div class="user-card-info-section">
                        <div class="info-row">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <div class="info-content">
                                <span class="info-label">Email</span>
                                <span class="info-value">{{ $user->email }}</span>
                            </div>
                        </div>
                        @if($user->department)
                        <div class="info-row">
                            <i class="fas fa-building" aria-hidden="true"></i>
                            <div class="info-content">
                                <span class="info-label">Department</span>
                                <span class="info-value">{{ $user->department->name }}</span>
                            </div>
                        </div>
                        @endif
                        @if($user->section)
                        <div class="info-row">
                            <i class="fas fa-users" aria-hidden="true"></i>
                            <div class="info-content">
                                <span class="info-label">Section</span>
                                <span class="info-value">{{ $user->section }}</span>
                            </div>
                        </div>
                        @endif
                        @if($user->room_number)
                        <div class="info-row">
                            <i class="fas fa-door-open" aria-hidden="true"></i>
                            <div class="info-content">
                                <span class="info-label">Room</span>
                                <span class="info-value">{{ $user->room_number }}</span>
                            </div>
                        </div>
                        @endif
                        <div class="info-row">
                            <i class="fas fa-trophy" aria-hidden="true"></i>
                            <div class="info-content">
                                <span class="info-label">Points</span>
                                <span class="info-value">{{ number_format($user->total_points ?? 0) }} pts</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-circle-check" aria-hidden="true"></i>
                            <div class="info-content">
                                <span class="info-label">Status</span>
                                <span class="info-value status-{{ $user->stat ? 'active' : 'pending' }}">{{ $user->status_display }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown-divider"></div>

                    {{-- Dark Mode Toggle --}}
                    <div class="user-card-toggles">
                        <div class="toggle-row">
                            <div class="toggle-label">
                                <i class="fas fa-moon" aria-hidden="true"></i>
                                <span>Dark Mode</span>
                            </div>
                            <label class="toggle-switch" for="user-card-dark-toggle">
                                <input type="checkbox" id="user-card-dark-toggle">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="dropdown-divider"></div>

                    {{-- Verify Email Warning --}}
                    @if(!$user->hasVerifiedEmail())
                    <a href="{{ route('settings.index') }}#profile" class="dropdown-item text-warning">
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                        Verify Email
                    </a>
                    @endif

                    {{-- Navigation Links --}}
                    <a href="{{ route('settings.index') }}" class="dropdown-item">
                        <i class="fas fa-cog" aria-hidden="true"></i>
                        Settings
                    </a>
                    <a href="{{ route('about') }}" class="dropdown-item">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        About EPAS-E
                    </a>
                    <a href="{{ route('contact') }}" class="dropdown-item">
                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        Contact Us
                    </a>
                    <a href="{{ route('help-support') }}" class="dropdown-item">
                        <i class="fas fa-question-circle" aria-hidden="true"></i>
                        Help & Support
                    </a>

                    <div class="dropdown-divider"></div>

                    {{-- Logout --}}
                    <button class="dropdown-item text-danger" id="logout-btn">
                        <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Logout Form -->
<form id="logout-form" action="{{ dynamic_route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- Avatar Upload Form (hidden) -->
<form id="navbar-avatar-form" action="{{ dynamic_route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" style="display: none;">
    @csrf
    <input type="file" id="navbar-avatar-upload" name="avatar" accept="image/*">
</form>

<!-- Avatar upload trigger script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var fileInput = document.getElementById('navbar-avatar-upload');
        var form = document.getElementById('navbar-avatar-form');
        if (!fileInput || !form) return;

        // All avatar triggers (mobile avatar, dropdown avatar)
        ['mobile-avatar-trigger', 'dropdown-avatar-trigger'].forEach(function(id) {
            var trigger = document.getElementById(id);
            if (!trigger) return;
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                fileInput.click();
            });
        });

        // Auto-submit when file is selected
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                form.submit();
            }
        });
    });
</script>

<!-- Dark mode toggle sync for user card -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toggle = document.getElementById('user-card-dark-toggle');
        if (!toggle) return;

        // Sync initial state
        toggle.checked = document.body.classList.contains('dark-mode');

        // Toggle dark mode when switch changes
        toggle.addEventListener('change', function() {
            var isDark = document.body.classList.contains('dark-mode');
            var newTheme = isDark ? 'light' : 'dark';
            document.body.classList.toggle('dark-mode', !isDark);
            document.documentElement.classList.toggle('dark-mode', !isDark);
            localStorage.setItem('theme', newTheme);
            // Update the navbar icon
            var icon = document.getElementById('dark-mode-icon');
            if (icon) icon.className = !isDark ? 'fas fa-sun' : 'fas fa-moon';
            // Notify other components
            window.dispatchEvent(new CustomEvent('themeChange', {
                detail: {
                    theme: newTheme
                }
            }));
        });

        // Sync when theme changes from other sources
        window.addEventListener('themeChange', function(e) {
            toggle.checked = (e.detail.theme === 'dark');
        });

        window.addEventListener('storage', function(e) {
            if (e.key === 'theme') {
                toggle.checked = (e.newValue === 'dark');
            }
        });
    });
</script>