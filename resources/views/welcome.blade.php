<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPAS-E LMS - Electronic Products Assembly and Servicing</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ dynamic_asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Vendor CSS -->
    <link href="{{ dynamic_asset('vendor/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ dynamic_asset('vendor/css/fontawesome.min.css') }}">

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ dynamic_asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/base/reset.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/base/typography.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/layout/header.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/layout/public-header.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/pages/welcome.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/buttons.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/utilities.css') }}">

    @vite(['resources/css/app.css'])
</head>

<body>
    @include('partials.header')

    <!-- Hero Section -->
    <section class="w-hero">
        <div class="w-hero-bg">
            @php
            $slides = ['epas1.jpg', 'epas2.jpg', 'epas3.jpg', 'epas4.jpg'];
            @endphp
            @foreach($slides as $index => $slide)
            <div class="w-slide {{ $index === 0 ? 'active' : '' }}"
                style="background-image: url('{{ dynamic_asset("assets/{$slide}") }}');"></div>
            @endforeach
        </div>
        <div class="w-hero-inner">
            <div class="w-hero-text">
                <div class="w-badge"><i class="fas fa-graduation-cap"></i> TESDA Accredited</div>
                <h1 class="w-title">Learn Electronics<br>Assembly & <span>Servicing</span></h1>
                <p class="w-desc">Empowering students with hands-on technical education and digital learning experiences through our comprehensive LMS platform.</p>
                <div class="w-actions">
                    @guest
                    <a href="{{ route('register') }}" class="w-btn w-btn-primary"><i class="fas fa-rocket"></i> Get Started</a>
                    <a href="#features" class="w-btn w-btn-secondary"><i class="fas fa-play-circle"></i> Learn More</a>
                    @else
                    <a href="{{ route('dashboard') }}" class="w-btn w-btn-primary"><i class="fas fa-chart-bar"></i> Go to Dashboard</a>
                    <a href="{{ route('courses.index') }}" class="w-btn w-btn-secondary"><i class="fas fa-book-open"></i> My Courses</a>
                    @endguest
                </div>
            </div>
            <div class="w-hero-cards">
                <div class="w-hcard">
                    <div class="w-hcard-icon" style="background:rgba(12,58,45,0.08);color:#0c3a2d"><i class="fas fa-laptop-code"></i></div>
                    <h4>Digital Learning</h4>
                    <p>Access courses and materials anytime, anywhere</p>
                </div>
                <div class="w-hcard">
                    <div class="w-hcard-icon" style="background:rgba(253,126,20,0.08);color:#fd7e14"><i class="fas fa-tools"></i></div>
                    <h4>Practical Skills</h4>
                    <p>Hands-on training in electronics assembly</p>
                </div>
                <div class="w-hcard">
                    <div class="w-hcard-icon" style="background:rgba(13,110,253,0.08);color:#0d6efd"><i class="fas fa-chart-line"></i></div>
                    <h4>Progress Tracking</h4>
                    <p>Monitor your learning journey in real-time</p>
                </div>
                <div class="w-hcard">
                    <div class="w-hcard-icon" style="background:rgba(111,66,193,0.08);color:#6f42c1"><i class="fas fa-certificate"></i></div>
                    <h4>Certification</h4>
                    <p>Earn recognized TESDA qualifications</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Bar -->
    <section class="w-stats">
        <div class="w-stats-inner">
            <div class="w-stat"><div class="w-stat-num">{{ $totalStudents ?? 0 }}</div><div class="w-stat-lbl">Active Students</div></div>
            <div class="w-stat"><div class="w-stat-num">{{ $totalInstructors ?? 0 }}</div><div class="w-stat-lbl">Instructors</div></div>
            <div class="w-stat"><div class="w-stat-num">{{ $totalCourses ?? 0 }}</div><div class="w-stat-lbl">Courses</div></div>
            <div class="w-stat"><div class="w-stat-num">{{ $totalModules ?? 0 }}</div><div class="w-stat-lbl">Modules</div></div>
        </div>
    </section>

    <!-- Features -->
    <section class="w-features" id="features">
        <div class="w-features-inner">
            <div class="w-section-badge"><i class="fas fa-star"></i> Features</div>
            <h2 class="w-section-title">Why Choose EPAS-E?</h2>
            <p class="w-section-desc">Everything you need for a complete technical education experience.</p>
            <div class="w-features-grid">
                <div class="w-feat">
                    <div class="w-feat-icon" style="background:rgba(12,58,45,0.08);color:#0c3a2d"><i class="fas fa-mobile-alt"></i></div>
                    <h4>Mobile Friendly</h4>
                    <p>Access your courses on any device — smartphones, tablets, and desktops.</p>
                </div>
                <div class="w-feat">
                    <div class="w-feat-icon" style="background:rgba(253,126,20,0.08);color:#fd7e14"><i class="fas fa-bolt"></i></div>
                    <h4>Fast & Reliable</h4>
                    <p>Lightning-fast performance with optimized loading and caching.</p>
                </div>
                <div class="w-feat">
                    <div class="w-feat-icon" style="background:rgba(25,135,84,0.08);color:#198754"><i class="fas fa-shield-alt"></i></div>
                    <h4>Secure Platform</h4>
                    <p>Enterprise-grade security with 2FA and comprehensive audit logging.</p>
                </div>
                <div class="w-feat">
                    <div class="w-feat-icon" style="background:rgba(13,110,253,0.08);color:#0d6efd"><i class="fas fa-tasks"></i></div>
                    <h4>Auto-Grading</h4>
                    <p>14+ question types with instant automated grading and feedback.</p>
                </div>
                <div class="w-feat">
                    <div class="w-feat-icon" style="background:rgba(111,66,193,0.08);color:#6f42c1"><i class="fas fa-trophy"></i></div>
                    <h4>Gamification</h4>
                    <p>Points, achievements, streaks, and leaderboards to keep you motivated.</p>
                </div>
                <div class="w-feat">
                    <div class="w-feat-icon" style="background:rgba(220,53,69,0.08);color:#dc3545"><i class="fas fa-file-pdf"></i></div>
                    <h4>Certificates</h4>
                    <p>Auto-generated PDF certificates with public verification links.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About -->
    <section class="w-about" id="about">
        <div class="w-about-inner">
            <div class="w-about-text">
                <div class="w-section-badge"><i class="fas fa-info-circle"></i> About</div>
                <h2 class="w-section-title" style="text-align:left">About EPAS-E LMS</h2>
                <p>The Electronic Products Assembly and Servicing Learning Management System provides comprehensive technical education in electronics assembly, repair, and maintenance.</p>
                <p>Combining theoretical knowledge with practical hands-on training, we prepare students for successful careers in the electronics industry.</p>
                <div class="w-about-actions">
                    @guest
                    <a href="{{ route('register') }}" class="w-btn w-btn-primary"><i class="fas fa-rocket"></i> Start Learning</a>
                    <a href="{{ route('contact') }}" class="w-btn w-btn-secondary"><i class="fas fa-envelope"></i> Contact Us</a>
                    @else
                    <a href="{{ route('dashboard') }}" class="w-btn w-btn-primary"><i class="fas fa-chart-bar"></i> Dashboard</a>
                    <a href="{{ route('credentials.index') }}" class="w-btn w-btn-secondary"><i class="fas fa-award"></i> My Credentials</a>
                    @endguest
                </div>
            </div>
            <div class="w-about-img">
                <img src="{{ dynamic_asset('assets/epas1.jpg') }}" alt="EPAS-E" loading="lazy"
                    onerror="this.style.display='none';this.parentElement.innerHTML='<div class=\'w-about-placeholder\'><i class=\'fas fa-microchip\'></i></div>'">
            </div>
        </div>
    </section>

    @include('partials.footer')

    @auth
    @include('components.bottom-nav')
    @endauth

    <!-- Scripts -->
    <script src="{{ dynamic_asset('vendor/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ dynamic_asset('js/utils/dark-mode.js') }}"></script>
    <script src="{{ dynamic_asset('js/components/public-darkmode.js') }}"></script>

    <!-- Slideshow -->
    <script>
    (function() {
        const slides = document.querySelectorAll('.w-slide');
        if (slides.length < 2) return;
        let current = 0;
        setInterval(function() {
            slides[current].classList.remove('active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('active');
        }, 5000);
    })();
    </script>

    @guest
    <script src="{{ dynamic_asset('js/public-header.js') }}"></script>
    @endguest

    @auth
    <script src="{{ dynamic_asset('js/public-header-auth.js') }}"></script>
    @endauth
</body>

</html>
