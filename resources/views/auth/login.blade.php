@extends('layouts.auth-layout')

@section('title', 'Login - EPAS-E LMS')

@section('content')
<div class="login-container">
    <h1 class="form-title">Login</h1>
    <form method="POST" action="{{ route('login') }}" id="loginForm" autocomplete="off">
        @csrf
        <x-login-fields autocompleteEmail="off" autocompletePassword="off" />

        <div class="form-row remember-row">
            <label class="remember-label">
                <input type="checkbox" name="remember" id="rememberMe">
                Remember me
            </label>
            <div style="margin-left: auto;">
                <a href="{{ route('password.request') }}" id="forgotPasswordLink">Forgot Password?</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert" data-auto-dismiss="5000">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" data-auto-dismiss="8000">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('verification_sent'))
            <div class="alert alert-info alert-dismissible fade show" role="alert" data-auto-dismiss="8000">
                <i class="fas fa-envelope me-2"></i>
                {{ session('verification_sent') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <button type="submit" class="btn-primary">Login</button>

        <div class="divider" role="separator" aria-orientation="horizontal">
            <span>or</span>
        </div>

        <div class="register">
            <p>Don't have an account?</p> <a href="{{ route('register') }}">Register here</a>
        </div>
    </form>
</div>

<style>
    .remember-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.9rem;
        color: var(--primary);
    }
    .remember-label input[type="checkbox"] {
        width: auto;
        margin: 0;
        cursor: pointer;
    }
</style>

<script>
(function() {
    const STORAGE_KEY = 'rememberedEmail';
    const rememberCheckbox = document.getElementById('rememberMe');
    const emailInput = document.getElementById('login_email');
    const passwordInput = document.getElementById('login_password');

    // Only load remembered email if remember me was previously checked
    const savedEmail = localStorage.getItem(STORAGE_KEY);
    if (savedEmail) {
        emailInput.value = savedEmail;
        rememberCheckbox.checked = true;
    } else {
        // Clear any browser autofill after a short delay
        setTimeout(function() {
            if (!rememberCheckbox.checked) {
                // Don't clear if user is actively typing
                if (document.activeElement !== emailInput && document.activeElement !== passwordInput) {
                    passwordInput.value = '';
                }
            }
        }, 100);
    }

    // Save email to localStorage only if remember me is checked
    // This will be saved on page load after successful login (server sets a flag)
    @if(session('login_success'))
    if (rememberCheckbox.checked && emailInput.value) {
        localStorage.setItem(STORAGE_KEY, emailInput.value);
    }
    @endif

    // Clear saved email if user unchecks remember me
    rememberCheckbox.addEventListener('change', function() {
        if (!this.checked) {
            localStorage.removeItem(STORAGE_KEY);
        }
    });
})();
</script>
@endsection
