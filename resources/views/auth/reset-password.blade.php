@extends('layouts.auth-layout')

@section('title', 'Reset Password - EPAS-E LMS')

@section('content')
<div class="login-container">
    <h1 class="form-title">Set New Password</h1>
    
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

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        
        <div class="input">
            <input
                type="email"
                id="email"
                name="email"
                placeholder=" "
                value="{{ $email ?? old('email') }}"
                required
                autofocus
                autocomplete="off">
            <label for="email">EMAIL</label>
        </div>

        <div class="input">
            <input
                type="password"
                id="password"
                name="password"
                placeholder=" "
                required
                autocomplete="off">
            <label for="password">NEW PASSWORD</label>

            <button
                type="button"
                class="toggle pw-toggle"
                data-target="password"
                aria-label="Toggle password visibility"
                aria-pressed="false">
                <i class="fa fa-eye" aria-hidden="true"></i>
            </button>
        </div>

        <div class="input">
            <input
                type="password"
                id="password-confirm"
                name="password_confirmation"
                placeholder=" "
                required
                autocomplete="off">
            <label for="password-confirm">CONFIRM PASSWORD</label>

            <button
                type="button"
                class="toggle pw-toggle"
                data-target="password-confirm"
                aria-label="Toggle password visibility"
                aria-pressed="false">
                <i class="fa fa-eye" aria-hidden="true"></i>
            </button>
        </div>

        <button type="submit" class="btn-primary">Reset Password</button>

        <div class="divider" role="separator" aria-orientation="horizontal">
            <span>or</span>
        </div>

        <div class="register" style="text-align: center;">
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
    </form>
</div>
@endsection
