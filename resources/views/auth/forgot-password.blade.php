@extends('layouts.auth-layout')

@section('title', 'Forgot Password - EPAS-E LMS')

@section('content')
<div class="login-container">
    <h1 class="form-title">Reset Password</h1>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" data-auto-dismiss="5000">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" data-auto-dismiss="5000">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="input">
            <input
                type="email"
                id="email"
                name="email"
                placeholder=" "
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="off">
            <label for="email">EMAIL</label>
        </div>

        <p style="margin-bottom: 1rem; font-size: 0.9rem; color: #6c757d;">
            Enter your email address and we'll send you a link to reset your password.
        </p>

        <button type="submit" class="btn-primary" id="submitBtn">
            <span class="btn-text"><i class="fas fa-paper-plane me-1"></i> Send Reset Link</span>
            <span class="btn-loading" style="display: none;">
                <i class="fas fa-spinner fa-spin me-1"></i> Sending...
            </span>
        </button>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const btn = document.getElementById('submitBtn');
            if (form && btn) {
                form.addEventListener('submit', function() {
                    btn.disabled = true;
                    btn.querySelector('.btn-text').style.display = 'none';
                    btn.querySelector('.btn-loading').style.display = 'inline';
                });
            }
        });
        </script>

        <div class="divider" role="separator" aria-orientation="horizontal">
            <span>or</span>
        </div>

        <div class="register" style="text-align: center;">
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
    </form>
</div>
@endsection
