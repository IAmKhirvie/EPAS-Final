@extends('layouts.auth-layout')

@section('title', 'Register - EPAS-E LMS')

@section('content')
<div class="login-container">
    <h1 class="form-title">Create Account</h1>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" data-auto-dismiss="5000">
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

    <form method="POST" action="{{ route('register') }}" id="registerForm" autocomplete="off">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="input">
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        placeholder=" "
                        value="{{ old('first_name') }}"
                        required
                        autofocus
                        autocomplete="off"
                        readonly
                        onfocus="this.removeAttribute('readonly')"
                        pattern="^[a-zA-Z\s\-'\.]+$"
                        title="Letters only — no numbers or special characters">
                    <label for="first_name">FIRST NAME</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="input">
                    <input
                        type="text"
                        id="middle_name"
                        name="middle_name"
                        placeholder=" "
                        value="{{ old('middle_name') }}"
                        autocomplete="off"
                        readonly
                        onfocus="this.removeAttribute('readonly')"
                        pattern="^[a-zA-Z\s\-'\.]+$"
                        title="Letters only — no numbers or special characters">
                    <label for="middle_name">MIDDLE NAME (Optional)</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="input">
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        placeholder=" "
                        value="{{ old('last_name') }}"
                        required
                        autocomplete="off"
                        readonly
                        onfocus="this.removeAttribute('readonly')"
                        pattern="^[a-zA-Z\s\-'\.]+$"
                        title="Letters only — no numbers or special characters">
                    <label for="last_name">LAST NAME</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input">
                    <input
                        type="text"
                        id="ext_name"
                        name="ext_name"
                        placeholder=" "
                        value="{{ old('ext_name') }}"
                        autocomplete="off"
                        readonly
                        onfocus="this.removeAttribute('readonly')"
                        pattern="^[a-zA-Z\s\-'\.]+$"
                        title="Letters only — no numbers or special characters">
                    <label for="ext_name">EXT. NAME</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="input">
                    <input
                        type="text"
                        id="student_id"
                        name="student_id"
                        placeholder=" "
                        value="{{ old('student_id', 'MAR') }}"
                        required
                        autocomplete="off"
                        readonly
                        onfocus="this.removeAttribute('readonly')"
                        pattern="^MAR.+$"
                        title="Student ID must start with MAR">
                    <label for="student_id">STUDENT ID (e.g. MAR-123456789)</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="input">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder=" "
                        value="{{ old('email') }}"
                        required
                        autocomplete="off"
                        readonly
                        onfocus="this.removeAttribute('readonly')">
                    <label for="email">EMAIL</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="input">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder=" "
                        required
                        autocomplete="new-password">
                    <label for="password">PASSWORD</label>

                    <button
                        type="button"
                        class="toggle pw-toggle"
                        data-target="password"
                        aria-label="Toggle password visibility"
                        aria-pressed="false">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- Password Strength Meter -->
                <div class="password-strength" id="passwordStrength"></div>

                <!-- Password Requirements -->
                <div class="password-requirements" id="passwordRequirements">
                    <div class="requirement unmet" id="reqLength"><i class="fas fa-times"></i> At least 8 characters</div>
                    <div class="requirement unmet" id="reqUppercase"><i class="fas fa-times"></i> One uppercase letter</div>
                    <div class="requirement unmet" id="reqLowercase"><i class="fas fa-times"></i> One lowercase letter</div>
                    <div class="requirement unmet" id="reqNumber"><i class="fas fa-times"></i> One number</div>
                    <div class="requirement unmet" id="reqSpecial"><i class="fas fa-times"></i> One special character</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="input">
                    <input
                        type="password"
                        id="password-confirm"
                        name="password_confirmation"
                        placeholder=" "
                        required
                        autocomplete="new-password"
                        readonly
                        onfocus="this.removeAttribute('readonly')">
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
                <div id="passwordMatch" class="password-requirements"></div>
            </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="checkbox-group">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms" style="font-size: 0.9rem;">
                I agree to the <span class="terms-link" onclick="openTermsModal()">Terms and Conditions</span>
                and <span class="terms-link" onclick="openPrivacyModal()">Privacy Policy</span>
            </label>
        </div>

        <div class="disclaimer" style="margin-bottom: 1rem; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">
            <p style="margin: 0; font-size: 0.85rem; color: #856404;">
                <i class="fas fa-exclamation-triangle me-1"></i>
                <strong>Important:</strong> After registration, you must:
            </p>
            <ol style="margin: 5px 0 0 0; padding-left: 25px; font-size: 0.85rem; color: #856404;">
                <li>Verify your email address (check inbox & spam)</li>
                <li>Wait for admin approval</li>
            </ol>
        </div>

        <button type="button" class="btn-primary" id="submitBtn" onclick="showConfirmModal()" disabled>
            <i class="fas fa-user-plus me-1"></i> Submit Registration
        </button>

        <div class="divider" role="separator" aria-orientation="horizontal">
            <span>or</span>
        </div>

        <div class="register" style="margin-top:1rem; text-align: center;">
            <p>Already have an account?</p> <a href="{{ route('login') }}">Login here</a>
        </div>
    </form>
</div>

<!-- Confirmation Modal -->
<div class="modal-overlay" id="confirmModal" style="display: none;">
    <div class="confirm-modal">
        <div class="confirm-header">
            <i class="fas fa-question-circle"></i>
            <h3>Confirm Registration</h3>
        </div>
        <div class="confirm-body">
            <p>Please confirm your details:</p>
            <div class="confirm-details">
                <div class="detail-row">
                    <span class="label">Name:</span>
                    <span class="value" id="confirmName"></span>
                </div>
                <div class="detail-row">
                    <span class="label">Email:</span>
                    <span class="value" id="confirmEmail"></span>
                </div>
            </div>
            <div class="confirm-warning">
                <i class="fas fa-info-circle"></i>
                <p>
                    A verification email will be sent to your email address.
                    Your account will need admin approval before you can log in.
                </p>
            </div>
            <p class="confirm-question"><strong>Are you sure you want to submit this registration?</strong></p>
        </div>
        <div class="confirm-actions">
            <button type="button" class="btn-secondary" onclick="hideConfirmModal()">
                <i class="fas fa-arrow-left me-1"></i> Go Back
            </button>
            <button type="button" class="btn-primary" id="confirmSubmitBtn" onclick="submitForm()">
                <span class="btn-text"><i class="fas fa-check me-1"></i> Yes, Submit</span>
                <span class="btn-loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin me-1"></i> Submitting...
                </span>
            </button>
        </div>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.confirm-modal {
    background: white;
    border-radius: 12px;
    max-width: 450px;
    width: 90%;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.confirm-header {
    background: #6d9773;
    color: white;
    padding: 20px;
    text-align: center;
    border-radius: 12px 12px 0 0;
}

.confirm-header i {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.confirm-header h3 {
    margin: 0;
    font-size: 1.3rem;
}

.confirm-body {
    padding: 20px;
}

.confirm-details {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.detail-row:last-child {
    margin-bottom: 0;
}

.detail-row .label {
    color: #6c757d;
    font-weight: 500;
}

.detail-row .value {
    color: #333;
    font-weight: 600;
}

.confirm-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 12px;
    display: flex;
    gap: 10px;
    margin: 15px 0;
}

.confirm-warning i {
    color: #856404;
    font-size: 1.2rem;
    margin-top: 2px;
}

.confirm-warning p {
    margin: 0;
    font-size: 0.9rem;
    color: #856404;
}

.confirm-question {
    text-align: center;
    margin-top: 15px;
    color: #333;
}

.confirm-actions {
    padding: 15px 20px 20px;
    display: flex;
    gap: 10px;
    justify-content: center;
}

.confirm-actions .btn-secondary {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.2s;
}

.confirm-actions .btn-secondary:hover {
    background: #5a6268;
}

.confirm-actions .btn-primary {
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.2s;
}

.confirm-actions .btn-primary:hover {
    background: #218838;
}

.confirm-actions .btn-primary:disabled {
    background: #6c757d;
    cursor: not-allowed;
}
</style>

<script>
function showConfirmModal() {
    const firstName = document.getElementById('first_name').value.trim();
    const middleName = document.getElementById('middle_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const extName = document.getElementById('ext_name').value.trim();
    const email = document.getElementById('email').value.trim();

    // Validate names — no numbers allowed
    const namePattern = /^[a-zA-Z\s\-'\.]+$/;
    const fields = [
        { value: firstName, label: 'First name' },
        { value: lastName, label: 'Last name' },
    ];
    if (middleName) fields.push({ value: middleName, label: 'Middle name' });
    if (extName) fields.push({ value: extName, label: 'Ext. name' });

    for (const field of fields) {
        if (!namePattern.test(field.value)) {
            alert(field.label + ' must contain only letters — no numbers or special characters.');
            return;
        }
    }

    // Build full name
    let fullName = firstName;
    if (middleName) fullName += ' ' + middleName;
    fullName += ' ' + lastName;
    if (extName) fullName += ' ' + extName;

    document.getElementById('confirmName').textContent = fullName;
    document.getElementById('confirmEmail').textContent = email;
    document.getElementById('confirmModal').style.display = 'flex';
}

function hideConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function submitForm() {
    const btn = document.getElementById('confirmSubmitBtn');
    btn.disabled = true;
    btn.querySelector('.btn-text').style.display = 'none';
    btn.querySelector('.btn-loading').style.display = 'inline';

    document.getElementById('registerForm').submit();
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideConfirmModal();
    }
});

// Close modal on overlay click
document.getElementById('confirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideConfirmModal();
    }
});
</script>
@endsection

@push('modals')
    @include('partials.terms-modal')
    @include('partials.privacy-modal')
@endpush
