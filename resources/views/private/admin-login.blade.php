@extends('layouts.auth-layout')

@section('title', 'Admin Login - EPAS-E LMS')

@section('content')
  <div class="login-container">
    <div class="login-header">
      <div class="login-icon admin-icon">
        <i class="fas fa-user-shield"></i>
      </div>
      <h1 class="form-title">Administrator Login</h1>
      <p class="login-subtitle">System Administration Portal</p>
    </div>

    <form method="POST" action="{{ route('admin.login') }}" id="adminLoginForm">
      @csrf

      <x-login-fields />

      <div class="form-row remember-row" style="display:flex; align-items:center; gap:.5rem; margin-bottom:1rem;">
        <label>
          <input type="checkbox" name="remember" id="rememberMeAdmin"> Remember me
        </label>
        <div style="margin-left:auto;">
          <a href="{{ route('password.request') }}">Forgot Password?</a>
        </div>
      </div>

      @if ($errors->any())
        <div class="alert alert-danger dismissable">
          {{ $errors->first() }}
          <button type="button" class="close-btn" aria-label="Dismiss">&times;</button>
        </div>
      @endif

      @if (session('status'))
        <div class="alert alert-success dismissable">
          {{ session('status') }}
        </div>
      @endif

      <button type="submit" class="btn-primary">
        <i class="fas fa-sign-in-alt me-2"></i> Login as Administrator
      </button>

      <div class="divider" role="separator" aria-orientation="horizontal">
        <span>Other Portals</span>
      </div>

      <div class="portal-links">
        <a href="{{ route('instructor.login') }}" class="portal-link instructor">
          <i class="fas fa-chalkboard-teacher"></i>
          <span>Instructor Login</span>
        </a>
        <a href="{{ route('login') }}" class="portal-link student">
          <i class="fas fa-user-graduate"></i>
          <span>Student Login</span>
        </a>
      </div>
    </form>
  </div>

  <style>
    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .login-icon {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      font-size: 2rem;
    }

    .login-icon.admin-icon {
      background: linear-gradient(135deg, #dc3545, #c82333);
      color: white;
    }

    .login-subtitle {
      color: var(--light-gray);
      font-size: 0.95rem;
      margin-top: 0.5rem;
    }

    .dark-mode .login-subtitle {
      color: #a0aec0;
    }

    .portal-links {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
    }

    .portal-link {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
      padding: 1rem;
      border-radius: 8px;
      text-decoration: none;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }

    .portal-link.instructor {
      background: rgba(23, 162, 184, 0.1);
      color: #17a2b8;
      border-color: rgba(23, 162, 184, 0.3);
    }

    .portal-link.instructor:hover {
      background: rgba(23, 162, 184, 0.2);
      border-color: #17a2b8;
    }

    .portal-link.student {
      background: rgba(40, 167, 69, 0.1);
      color: #28a745;
      border-color: rgba(40, 167, 69, 0.3);
    }

    .portal-link.student:hover {
      background: rgba(40, 167, 69, 0.2);
      border-color: #28a745;
    }

    .portal-link i {
      font-size: 1.5rem;
    }

    .portal-link span {
      font-size: 0.85rem;
      font-weight: 500;
    }

    .dark-mode .portal-link.instructor {
      background: rgba(23, 162, 184, 0.15);
    }

    .dark-mode .portal-link.student {
      background: rgba(40, 167, 69, 0.15);
    }

    @media (max-width: 480px) {
      .portal-links {
        flex-direction: column;
      }

      .login-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
      }
    }
  </style>

  <script>
  (function() {
      const STORAGE_KEY = 'rememberedAdminEmail';

      // Load remembered email on page load
      const savedEmail = localStorage.getItem(STORAGE_KEY);
      if (savedEmail) {
          document.getElementById('login_email').value = savedEmail;
          document.getElementById('rememberMeAdmin').checked = true;
      }

      // Handle form submission
      document.getElementById('adminLoginForm').addEventListener('submit', function() {
          const rememberMe = document.getElementById('rememberMeAdmin').checked;
          const email = document.getElementById('login_email').value;

          if (rememberMe && email) {
              localStorage.setItem(STORAGE_KEY, email);
          } else {
              localStorage.removeItem(STORAGE_KEY);
          }
      });

      // Clear saved email if user unchecks remember me
      document.getElementById('rememberMeAdmin').addEventListener('change', function() {
          if (!this.checked) {
              localStorage.removeItem(STORAGE_KEY);
          }
      });
  })();
  </script>
@endsection
