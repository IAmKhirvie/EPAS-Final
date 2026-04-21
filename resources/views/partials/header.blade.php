<!-- Public Navbar — guest sees nav links + Login/Register, authenticated sees icon buttons -->
<header class="top-navbar lobby-navbar" id="mainNavbar">

    <!-- Left: Logo -->
    <div class="navbar-left-2">
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

    @guest
    <!-- Center: Nav links (guest only) -->
    <nav class="pub-nav-links">
        <a href="#features">Features</a>
        <a href="#about">About</a>
        <a href="{{ route('contact') }}">Contact</a>
    </nav>

    <!-- Right: Login + Register buttons -->
    <div class="pub-nav-right">
        <button class="icon-button" id="dark-mode-toggle" aria-label="Toggle dark mode">
            <i class="fas fa-moon" id="dark-mode-icon" aria-hidden="true"></i>
        </button>

        <!-- Login button with portal dropdown -->
        <div class="navbar-item">
            <button class="pub-btn pub-btn-outline" id="login-dropdown-btn">Log in</button>
            <div class="dropdown" id="login-dropdown">
                <div class="dropdown-content">
                    <div class="login-modal-header">
                        <h6>Choose Login Portal</h6>
                    </div>
                    <a class="dropdown-item login-option admin" href="{{ route('admin.login') }}">
                        <i class="fas fa-user-shield"></i>
                        <div><strong>Admin Login</strong><small class="d-block">System Administration</small></div>
                    </a>
                    <a class="dropdown-item login-option instructor" href="{{ route('instructor.login') }}">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <div><strong>Instructor Login</strong><small class="d-block">Teaching Portal</small></div>
                    </a>
                    <a class="dropdown-item login-option student" href="{{ route('login') }}">
                        <i class="fas fa-user-graduate"></i>
                        <div><strong>Student Login</strong><small class="d-block">Learning Portal</small></div>
                    </a>
                </div>
            </div>
        </div>

        <a href="{{ route('register') }}" class="pub-btn pub-btn-primary">Register</a>
    </div>
    @endguest

    @auth
    <!-- Right: Authenticated user icons (same as dashboard navbar) -->
    <div class="navbar-right">
        <div class="navbar-item">
            <a class="icon-button" href="{{ route('welcome') }}" title="Home"><i class="fa-solid fa-house"></i></a>
        </div>
        <div class="navbar-item">
            <button class="icon-button" id="dark-mode-toggle" aria-label="Toggle dark mode">
                <i class="fas fa-moon" id="dark-mode-icon" aria-hidden="true"></i>
            </button>
        </div>
        <div class="navbar-item">
            <a class="icon-button" href="{{ route('dashboard') }}" title="Dashboard"><i class="fas fa-chart-bar"></i></a>
        </div>

        @php $user = Auth::user(); @endphp

        <!-- Notifications -->
        <div class="navbar-item">
            <button class="icon-button" id="notifications-btn" title="Announcements" aria-label="Announcements">
                <i class="fas fa-bell" aria-hidden="true"></i>
                @if(isset($recentAnnouncementsCount) && $recentAnnouncementsCount > 0)
                <span class="notification-badge" id="notification-badge">{{ $recentAnnouncementsCount }}</span>
                @endif
            </button>
            <div class="popover notifications-popover" id="notifications-popover">
                <div class="popover-header">
                    <div class="header-left"><i class="fas fa-bullhorn me-2"></i><span>Announcements</span></div>
                    <a href="{{ route('private.announcements.index') }}" class="view-all-btn"><i class="fas fa-list me-1"></i>View All</a>
                </div>
                <div class="notifications-list" id="notifications-list">
                    @php $notifications = isset($recentAnnouncements) ? $recentAnnouncements : collect(); @endphp
                    @if($notifications->count() > 0)
                    @foreach($notifications as $announcement)
                    <div class="notification-item {{ $announcement->is_urgent ?? false ? 'urgent' : '' }}" data-announcement-id="{{ $announcement->id }}">
                        <a href="{{ route('private.announcements.show', $announcement) }}" class="notification-link">
                            <div class="notification-icon">
                                @if($announcement->is_urgent ?? false)<i class="fas fa-exclamation-circle urgent-icon"></i>
                                @elseif($announcement->is_pinned ?? false)<i class="fas fa-thumbtack pinned-icon"></i>
                                @else<i class="fas fa-bell regular-icon"></i>@endif
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">{{ Str::limit($announcement->title, 45) }}</div>
                                <div class="notification-message">{{ Str::limit(strip_tags($announcement->content ?? ''), 70) }}</div>
                                <div class="notification-meta">
                                    <span class="notification-time"><i class="fas fa-calendar me-1"></i>{{ $announcement->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                    @else
                    <div class="notification-item empty">
                        <div class="notification-content text-center py-4">
                            <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                            <div class="empty-text">No announcements yet</div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="popover-footer">
                    <a href="{{ route('private.announcements.index') }}" class="view-all-link-footer">
                        <i class="fas fa-th-list me-2"></i>View All Announcements<i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- User menu -->
        <div class="navbar-item">
            <button class="user-button user-button--desktop" id="user-menu-btn">
                <div class="icon-button nav-avatar-btn" id="desktop-avatar-trigger" title="{{ $user->full_name }}">
                    @if($user->profile_image)<img src="{{ $user->profile_image_url }}" alt="" class="nav-avatar-img">
                    @else<span class="nav-avatar-initials">{{ $user->initials }}</span>@endif
                </div>
            </button>
            <button class="user-button user-button--mobile" id="user-menu-btn-mobile">
                <div class="avatar" id="mobile-avatar-trigger">
                    <img src="{{ $user->profile_image_url }}" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
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
                    <div class="user-card-header" id="dropdown-avatar-trigger" title="Change photo"
                        style="cursor:pointer;{{ $user->profile_image ? 'background-image:url('.$user->profile_image_url.');' : '' }}">
                        <div class="user-card-header-overlay"></div>
                        @if(!$user->profile_image)<span class="user-card-header-initials">{{ $user->initials }}</span>@endif
                        <div class="user-card-header-info">
                            <h2 class="user-card-name">{{ $user->full_name }}</h2>
                            <h3 class="user-card-subtitle">
                                <span class="user-card-role-badge role-{{ $user->role }}">{{ $user->role_display }}</span>
                                @if($user->student_id)<span class="user-card-id">ID: {{ $user->student_id }}</span>@endif
                            </h3>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="user-card-info-section">
                        <div class="info-row"><i class="fas fa-envelope"></i><div class="info-content"><span class="info-label">Email</span><span class="info-value">{{ $user->email }}</span></div></div>
                        @if($user->department)<div class="info-row"><i class="fas fa-building"></i><div class="info-content"><span class="info-label">Department</span><span class="info-value">{{ $user->department->name }}</span></div></div>@endif
                        <div class="info-row"><i class="fas fa-trophy"></i><div class="info-content"><span class="info-label">Points</span><span class="info-value">{{ number_format($user->total_points ?? 0) }} pts</span></div></div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('settings.index') }}"><i class="fas fa-cog"></i>Settings</a>
                    <a class="dropdown-item" href="{{ route('credentials.index') }}"><i class="fas fa-award"></i>Credentials</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('about') }}"><i class="fas fa-info-circle"></i>About</a>
                    <a class="dropdown-item" href="{{ route('contact') }}"><i class="fas fa-envelope"></i>Contact Us</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i>Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endauth
</header>
