<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <title>@yield('title', 'EPAS-E LMS')</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ dynamic_asset('favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ dynamic_asset('favicon.png') }}">

  <!-- Google Fonts - Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

  <!-- Bootstrap CSS (local) -->
  <link href="{{ dynamic_asset('vendor/css/bootstrap.min.css') }}" rel="stylesheet">
  <!-- Font Awesome (local) -->
  <link rel="stylesheet" href="{{ dynamic_asset('vendor/css/fontawesome.min.css') }}">

  <!-- App CSS -->
  <link rel="stylesheet" href="{{ dynamic_asset('css/app.css') }}">
  <link rel="stylesheet" href="{{ dynamic_asset('css/base/reset.css') }}">
  <link rel="stylesheet" href="{{ dynamic_asset('css/base/typography.css') }}">
  <link rel="stylesheet" href="{{ dynamic_asset('css/layout/header.css') }}">
  <link rel="stylesheet" href="{{ dynamic_asset('css/layout/public-header.css') }}">
  <link rel="stylesheet" href="{{ dynamic_asset('css/pages/auth.css') }}">
  <link rel="stylesheet" href="{{ dynamic_asset('css/components/buttons.css') }}">
  <link rel="stylesheet" href="{{ dynamic_asset('css/components/forms.css') }}">
  <link rel="stylesheet" href="{{ dynamic_asset('css/components/utilities.css') }}">
  <link rel="stylesheet" href="{{ dynamic_asset('css/components/error-popup.css') }}">

  @vite(['resources/css/app.css'])
  
  <style>
    /* Slideshow styles from lobby */
    .slideshow-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }
    
    .slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        opacity: 0;
        transform: scale(1.1);
        transition: transform 10s ease, opacity 1.5s ease;
    }
    
    .slide::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: color-mix(in srgb, var(--primary-dark) 80%, transparent);
    }
    
    .slide.active {
        opacity: 1;
        transform: scale(1);
    }

    /* Additional auth page styling */
    .auth-page-body {
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    @media (max-width: 1032px) {
        html {
            height: auto !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
        }
        body.auth-page-body {
            height: auto !important;
            overflow: hidden !important;
            max-width: 100vw;
        }
        .auth-content-container {
            overflow: hidden !important;
            max-width: 100vw;
        }
        .login-container {
            position: static !important;
            height: auto !important;
            overflow: hidden !important;
            max-width: 100%;
        }
        .lobby-navbar {
            padding: 0.5rem 0.75rem !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
            max-width: 100vw;
        }
    }
  </style>
</head>
<body class="auth-page-body">
  {{-- Page Loader for auth transitions --}}
  @include('components.page-loader')

  @include('partials.header')

    <!-- In auth-layout.blade.php -->
    <div class="slideshow-container" id="authSlideshow">
        @php
            $slides = [
                'epas1.jpg',
                'epas2.jpg', 
                'epas3.jpg',
                'epas4.jpg'
            ];
        @endphp
        
        @foreach($slides as $index => $slide)
            <div class="slide {{ $index === 0 ? 'active' : '' }}" 
                style="background-image: url('{{ dynamic_asset("assets/{$slide}") }}');"></div>
        @endforeach
    </div>

  <div class="auth-content-container">
    @yield('content')
  </div>

  <footer class="mobile-auth-footer">
    @hasSection('footer')
      {!! $__env->yieldContent('footer') !!}
    @else
      © {{ date('Y') }} IETI. All rights reserved.
    @endif
  </footer>

  <!-- Bootstrap JS (local) -->
  <script src="{{ dynamic_asset('vendor/js/bootstrap.bundle.min.js') }}"></script>
  
  <!-- Utility Scripts -->
  <script src="{{ dynamic_asset('js/utils/slideshow.js')}}"></script>
  <script src="{{ dynamic_asset('js/utils/dark-mode.js')}}"></script>

  <!-- Auth & Header Scripts -->
  <script src="{{ dynamic_asset('js/auth.js')}}"></script>
  <script src="{{ dynamic_asset('js/public-header.js')}}"></script>

  <!-- Initialize dark mode for auth pages -->
  <script>if (window.initDarkMode) window.initDarkMode();</script>

  <!-- Page Loader Logic for Auth Pages -->
  <script>
  (function() {
    var loader = document.getElementById('page-loader');

    // Hide loader immediately when DOM is ready
    function hideLoaderNow() {
      if (loader && !loader.classList.contains('hidden')) {
        loader.classList.add('hidden');
        setTimeout(function() {
          if (window._circuitAnimation) {
            cancelAnimationFrame(window._circuitAnimation);
            delete window._circuitAnimation;
          }
          if (loader.parentNode) loader.remove();
        }, 300);
      }
    }

    // Hide as soon as DOM is interactive (don't wait for images)
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
      hideLoaderNow();
    } else {
      document.addEventListener('DOMContentLoaded', hideLoaderNow);
    }
  })();
  </script>

  <!-- Global Error Popup Handler -->
  <div class="error-popup-overlay" id="errorPopupOverlay">
    <div class="error-popup">
      <div class="error-popup-header">
        <div class="error-popup-icon error" id="errorPopupIcon"><i class="fas fa-exclamation-circle"></i></div>
        <h3 class="error-popup-title" id="errorPopupTitle">Error</h3>
      </div>
      <div class="error-popup-body" id="errorPopupBody">An error occurred.</div>
      <div class="error-popup-footer">
        <button class="error-popup-btn error-popup-btn-primary" id="errorPopupCloseBtn">OK</button>
      </div>
    </div>
  </div>

  <script src="{{ dynamic_asset('js/error-popup.js') }}"></script>
  <script>
    // Show server-side flash messages via error popup
    @if(session('error_popup'))
      window.showErrorPopup(@json(session('error_popup')), @json(session('error_code') ? 'Error ' . session('error_code') : 'Error'), 'error');
    @endif
    @if(session('error'))
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

  <!-- Additional Scripts -->
  @stack('scripts')

  <!-- Modal Functions -->
  <script>
    function openTermsModal() {
        const modal = document.getElementById('termsModal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function openPrivacyModal() {
        const modal = document.getElementById('privacyModal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.style.display = 'none';
            });
        }
    });
  </script>
  
  <!-- Modals Stack -->
  @stack('modals')
</body>
</html>