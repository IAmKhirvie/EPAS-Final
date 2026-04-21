<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    {{-- Cache headers handled by middleware for authenticated pages --}}
    <title>@yield('title','EPAS-E - Electronic Products Assembly and Servicing')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ dynamic_asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ dynamic_asset('favicon.png') }}">

    <!-- Google Fonts - Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

    <!-- PWA Support -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0c3a2d">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="EPAS-E">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

    <!-- CSS (local) -->
    <link rel="stylesheet" href="{{ dynamic_asset('vendor/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('vendor/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('vendor/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/app.css') }}">
    
    <!-- Base CSS-->
    <link rel="stylesheet" href="{{ dynamic_asset('css/base/reset.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/base/typography.css') }}">
    
    <!-- Component CSS -->
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/adduser.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/alerts.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/buttons.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/fab.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/forms.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/overlay.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/tables.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/utilities.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/empty-state.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/skeleton.css') }}">

    <!-- Layout CSS -->
    <link rel="stylesheet" href="{{ dynamic_asset('css/layout/header.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/layout/main-content.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/layout/sidebar.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/layout/footer.css') }}">

    <!-- Page Specific CSS -->
    <link rel="stylesheet" href="{{ dynamic_asset('css/pages/modules.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/pages/users.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/pages/index.css')}}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/pages/common-page.css') }}">
    {{-- dashboard CSS loaded per-page via @push('styles') --}}
    <link rel="stylesheet" href="{{ dynamic_asset('css/pages/grades.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/pages/analytics.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/pages/content-builder.css') }}">

    <!-- Mobile CSS -->
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/responsive-tables.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/touch-friendly.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/pages/mobile.css') }}"  media="screen and (max-width: 1032px)">
    <script>
        // Immediately check and apply dark mode before page renders
        (function() {
            // Sync server-side theme cookie to localStorage
            var cookieMatch = document.cookie.match(/(?:^|; )theme=([^;]*)/);
            if (cookieMatch && cookieMatch[1] && cookieMatch[1] !== localStorage.getItem('theme')) {
                localStorage.setItem('theme', cookieMatch[1]);
            }

            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            // Apply theme immediately to prevent flash
            if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                document.documentElement.classList.add('dark-mode');
            } else {
                document.documentElement.classList.remove('dark-mode');
            }

            // Store the initial theme for reference
            window.initialTheme = savedTheme || (systemPrefersDark ? 'dark' : 'light');

            // Apply sidebar collapsed state immediately to prevent flash
            // Default: collapsed (unless user explicitly expanded it)
            var sidebarState = localStorage.getItem('sidebarCollapsed');
            if (sidebarState === null || sidebarState === 'true') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>
    @livewireStyles
    @stack('styles')
</head>
<body class="modern-layout" data-user-role="{{ auth()->user()->role ?? '' }}">

  {{-- Page Loader - Shows during page transitions --}}
  @include('components.page-loader')

  {{-- Header --}}
  @include('partials.navbar')

  <div class="overlay" id="overlay"></div>

  <div class="layout-wrapper">
    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Main Content --}}
    <main class="main-content" role="main" tabindex="-1">
        @yield('content')
    </main>
  </div>

  <!-- Bottom Navigation for Mobile -->
  @auth
  @include('components.bottom-nav')
  @endauth

  <!-- FAB Menu & Slide Sidebars -->
@auth
    @if(auth()->user()->role != 'student')
        @include('partials.fab-menu')
        @include('partials.sidebar-create-course')
        @include('partials.sidebar-create-module')
        @include('partials.sidebar-create-announcement')
        @include('partials.sidebar-add-user')

        <div class="fab-backdrop" id="fab-backdrop"></div>
    @endif
@endauth

  <!-- Scripts (local) - Critical -->
    <script src="{{ dynamic_asset('vendor/js/bootstrap.bundle.min.js') }}" data-navigate-once></script>

    <!-- App Script -->
    <script src="{{ dynamic_asset('js/app.js') }}" data-navigate-once></script>

    <!-- Utility Scripts -->
    <script src="{{ dynamic_asset('js/utils/dark-mode.js') }}" data-navigate-once></script>

    <!-- Component Script -->
    <script src="{{ dynamic_asset('js/components/navbar.js') }}" data-navigate-once></script>

    <!-- Deferred Scripts (non-critical) -->
    <script src="{{ dynamic_asset('vendor/js/gsap.min.js') }}" defer data-navigate-once></script>
    <script src="{{ dynamic_asset('js/utils/dynamic-form.js') }}" defer data-navigate-once></script>
    <script src="{{ dynamic_asset('js/functions/FAB.js') }}" defer data-navigate-once></script>
  @yield('scripts')
  @stack('scripts')

  <!-- Global Error Popup Handler -->
  <link rel="stylesheet" href="{{ dynamic_asset('css/components/error-popup.css') }}">

  <div class="error-popup-overlay" id="errorPopupOverlay">
    <div class="error-popup">
      <div class="error-popup-header">
        <div class="error-popup-icon error" id="errorPopupIcon">
          <i class="fas fa-exclamation-circle"></i>
        </div>
        <h3 class="error-popup-title" id="errorPopupTitle">Error</h3>
      </div>
      <div class="error-popup-body" id="errorPopupBody">
        An error occurred.
      </div>
      <div class="error-popup-footer">
        <button class="error-popup-btn error-popup-btn-primary" id="errorPopupCloseBtn">OK</button>
      </div>
    </div>
  </div>

  <script src="{{ dynamic_asset('js/error-popup.js') }}" data-navigate-once></script>
  <script>
    // Show server-side flash messages via error popup
    @if(session('error_popup'))
      window.showErrorPopup(
        @json(session('error_popup')),
        @json(session('error_code') ? 'Error ' . session('error_code') : 'Error'),
        'error'
      );
    @endif
    @if(session('error') && !session('success'))
      window.showErrorPopup(@json(session('error')), 'Error', 'error');
    @endif
    @if(session('success'))
      window.showErrorPopup(@json(session('success')), 'Success', 'info');
    @endif
    @if(session('warning'))
      window.showErrorPopup(@json(session('warning')), 'Warning', 'warning');
    @endif
    {{-- Debug info → console only (never visible in DOM) --}}
    @if(session('error_debug'))
      console.groupCollapsed('%c[JOMS Server Debug]%c Error details (not shown to user)',
        'color: #dc3545; font-weight: bold;', 'color: #6c757d;'
      );
      console.error(@json(session('error_debug')));
      console.log('Page:', @json(request()->fullUrl()));
      console.log('Time:', new Date().toISOString());
      console.groupEnd();
    @endif
  </script>

  @include('partials.pwa-scripts')
  @livewireScripts

  {{-- Re-initialize components after Livewire SPA navigation --}}
  <script data-navigate-once>
    document.addEventListener('livewire:navigated', () => {
        // Re-bind navbar event listeners on new DOM
        try { if (typeof TopNavbar !== 'undefined') new TopNavbar(); } catch(e) {}
        try { if (typeof initDarkMode !== 'undefined') initDarkMode(); } catch(e) {}

        // Re-apply sidebar collapsed state after SPA navigation
        var sidebar = document.getElementById('sidebar');
        if (sidebar) {
            var state = localStorage.getItem('sidebarCollapsed');
            var shouldCollapse = state === null || state === 'true';
            if (shouldCollapse) {
                sidebar.classList.add('collapsed');
                document.body.classList.add('sidebar-collapsed');
            } else {
                sidebar.classList.remove('collapsed');
                document.body.classList.remove('sidebar-collapsed');
            }
            var icon = document.querySelector('#sidebar-toggle i, #hamburger-menu i');
            if (icon) icon.className = shouldCollapse ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left';
        }
    });
  </script>

  {{-- Global Search --}}
  <script data-navigate-once>
  (function() {
      var input = document.getElementById('global-search');
      var box = document.getElementById('search-results');
      if (!input || !box) return;
      var timer = null;

      input.addEventListener('input', function() {
          clearTimeout(timer);
          var q = this.value.trim();
          if (q.length < 2) { box.classList.remove('active'); box.innerHTML = ''; return; }
          box.innerHTML = '<div class="sr-empty"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
          box.classList.add('active');
          timer = setTimeout(function() {
              fetch('/search?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                  .then(function(r) { return r.json(); })
                  .then(function(data) {
                      if (!data.length) { box.innerHTML = '<div class="sr-empty">No results for "' + q + '"</div>'; return; }
                      box.innerHTML = data.map(function(r) {
                          return '<a href="' + r.url + '" class="sr-item">' +
                              '<div class="sr-icon"><i class="' + r.icon + '"></i></div>' +
                              '<div class="sr-info"><span class="sr-title">' + r.title + '</span><span class="sr-sub">' + r.sub + '</span></div>' +
                              '<span class="sr-type">' + r.type + '</span></a>';
                      }).join('');
                  })
                  .catch(function() { box.innerHTML = '<div class="sr-empty">Search failed</div>'; });
          }, 300);
      });

      input.addEventListener('focus', function() { if (this.value.trim().length >= 2 && box.innerHTML) box.classList.add('active'); });
      document.addEventListener('click', function(e) { if (!e.target.closest('.navbar-search')) box.classList.remove('active'); });
  })();
  </script>

  {{-- Notification: mark as read + auto-refresh badge --}}
  <script>
  function markRead(el) {
      el.classList.add('read');
      // Decrement badge
      var badge = document.getElementById('notification-badge');
      if (badge) {
          var count = parseInt(badge.textContent) - 1;
          badge.textContent = Math.max(count, 0);
          if (count <= 0) badge.style.display = 'none';
      }
  }

  // Auto-refresh notification count every 30 seconds
  setInterval(function() {
      fetch('/api/announcements/unread-count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(function(r) { return r.json(); })
          .then(function(data) {
              var badge = document.getElementById('notification-badge');
              if (badge && typeof data.count !== 'undefined') {
                  badge.textContent = data.count;
                  badge.style.display = data.count > 0 ? '' : 'none';
              }
          })
          .catch(function() {});
  }, 30000);
  </script>
</body>
</html>