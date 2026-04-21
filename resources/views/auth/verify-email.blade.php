@extends('layouts.auth-layout')

@section('title', 'Verify Your Email - EPAS-E LMS')

@section('content')
<div class="verification-container">
    <div class="verification-icon">
        <i class="fas fa-envelope-open-text"></i>
    </div>

    <h2>Verify Your Email Address</h2>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" data-auto-dismiss="5000">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" data-auto-dismiss="8000">
            <i class="fas fa-exclamation-circle me-2"></i>
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="verification-message">
        <p>We've sent a verification link to:</p>
        <p class="email-display"><strong>{{ Auth::user()->email ?? 'your email address' }}</strong></p>
        <p class="text-muted">Please check your inbox and click the verification link to activate your account.</p>
    </div>

    <div class="verification-tips">
        <h6><i class="fas fa-lightbulb me-1"></i> Tips:</h6>
        <ul>
            <li>Check your <strong>spam/junk folder</strong> if you don't see the email</li>
            <li>The verification link expires in <strong>60 minutes</strong></li>
            <li>Make sure you're checking the correct email address</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('verification.send') }}" id="resendForm">
        @csrf
        <button type="submit" class="btn-primary" id="resendBtn">
            <span class="btn-text"><i class="fas fa-paper-plane me-1"></i> Resend Verification Email</span>
            <span class="btn-loading" style="display: none;">
                <i class="fas fa-spinner fa-spin me-1"></i> Sending...
            </span>
        </button>
    </form>

    <div class="verification-footer">
        <a href="{{ route('login') }}" class="btn-link">
            <i class="fas fa-arrow-left me-1"></i> Return to Login
        </a>
        <span class="divider">|</span>
        <a href="{{ route('settings.index') }}" class="btn-link">
            <i class="fas fa-cog me-1"></i> Settings
        </a>
    </div>
</div>
@endsection

@push('styles')
<style>
    .verification-container {
        max-width: 500px;
        margin: 40px auto;
        padding: 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        text-align: center;
    }

    .verification-icon {
        font-size: 4rem;
        color: #ffb902;
        margin-bottom: 1rem;
        animation: bounce 2s ease infinite;
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        60% { transform: translateY(-5px); }
    }

    .verification-container h2 {
        color: #333;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }

    .verification-message {
        margin-bottom: 1.5rem;
    }

    .email-display {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 8px;
        color: #0c3a2d;
        font-size: 1.1rem;
        margin: 10px 0;
    }

    .verification-tips {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 1.5rem;
        text-align: left;
    }

    .verification-tips h6 {
        color: #856404;
        margin-bottom: 10px;
    }

    .verification-tips ul {
        margin: 0;
        padding-left: 20px;
        color: #856404;
    }

    .verification-tips li {
        margin-bottom: 5px;
    }

    .btn-primary {
        display: inline-block;
        padding: 12px 28px;
        background: #ffb902;
        color: #0c3a2d;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .btn-primary:hover {
        background: #e5a702;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255,185,2,0.3);
    }

    .btn-primary:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .verification-footer {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
    }

    .btn-link {
        color: #6c757d;
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.2s ease;
    }

    .btn-link:hover {
        color: #ffb902;
    }

    .divider {
        color: #ddd;
        margin: 0 15px;
    }

    .alert {
        text-align: left;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resendForm');
    const btn = document.getElementById('resendBtn');
    const btnText = btn.querySelector('.btn-text');
    const btnLoading = btn.querySelector('.btn-loading');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Disable button and show loading
        btn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';

        const formData = new FormData(form);

        fetch("{{ route('verification.send') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', data.message || 'Verification email sent! Please check your inbox.');
            } else {
                // Show error message
                showAlert('danger', data.message || 'Failed to send verification email. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred. Please try again.');
        })
        .finally(() => {
            // Re-enable button and reset text
            btn.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
        });
    });

    function showAlert(type, message) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());

        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Insert after the h2
        const container = document.querySelector('.verification-container');
        const h2 = container.querySelector('h2');
        h2.after(alertDiv);

        // Auto-dismiss after 5 seconds for success
        if (type === 'success') {
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    }
});
</script>
@endpush
