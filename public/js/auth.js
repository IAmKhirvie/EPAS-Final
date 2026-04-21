// ---------- slideshow ----------
// Delegates to shared utils/slideshow.js (loaded before this file)
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initSlideshow === 'function') initSlideshow();
});

// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert[data-auto-dismiss]');
    alerts.forEach(alert => {
        const dismissTime = parseInt(alert.getAttribute('data-auto-dismiss')) || 5000;
        setTimeout(() => {
            if (alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, dismissTime);
    });
});

// ---------- password toggle ----------
(function () {
    function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
    document.querySelectorAll('.pw-toggle').forEach(btn => {
        btn.type = 'button';

        btn.addEventListener('click', function () {
        var id = this.dataset.target;
        var input = document.getElementById(id);
        var icon = this.querySelector('i');
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            }
            this.setAttribute('aria-pressed', 'true');
        } else {
            input.type = 'password';
            if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            }
            this.setAttribute('aria-pressed', 'false');
        }
        });

        // keyboard accessibility (Enter/Space)
        btn.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
        }
        });
    });
    });
})();

// ---------- Toast Notification Function ----------
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    
    if (!toast || !toastMessage) {
        return;
    }
    
    // Set message and type
    toastMessage.textContent = message;
    toast.className = 'toast-notification show';
    toast.classList.add(type);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.classList.remove(type), 300);
    }, 5000);
}

//---- Error Dismiss ----
document.addEventListener('DOMContentLoaded', function() {
    // Handle dismissable error alerts
    document.querySelectorAll('.alert.dismissable').forEach(alert => {
    const closeBtn = alert.querySelector('.close-btn');

    // Manual dismiss
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
        fadeOutAndRemove(alert);
        });
    }

    // Auto dismiss after 3s
    setTimeout(() => {
        fadeOutAndRemove(alert);
    }, 3000);
    });

    // Fade out helper
    function fadeOutAndRemove(element) {
    element.style.transition = 'opacity 0.5s ease';
    element.style.opacity = '0';
    setTimeout(() => element.remove(), 500);
    }
});

// ---------- Password Validation (Only runs on pages with password fields) ----------
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password-confirm');
    const passwordStrength = document.getElementById('passwordStrength');
    const submitBtn = document.getElementById('submitBtn');
    const termsCheckbox = document.getElementById('terms');

    // Only initialize password validation if the main password input exists
    if (passwordInput) {
        // Password requirements elements
        const reqLength = document.getElementById('reqLength');
        const reqUppercase = document.getElementById('reqUppercase');
        const reqLowercase = document.getElementById('reqLowercase');
        const reqNumber = document.getElementById('reqNumber');
        const reqSpecial = document.getElementById('reqSpecial');

        function validatePassword() {
            const password = passwordInput.value;
            
            // Check requirements
            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

            // Update requirement indicators if they exist
            if (reqLength) updateRequirement(reqLength, hasLength);
            if (reqUppercase) updateRequirement(reqUppercase, hasUppercase);
            if (reqLowercase) updateRequirement(reqLowercase, hasLowercase);
            if (reqNumber) updateRequirement(reqNumber, hasNumber);
            if (reqSpecial) updateRequirement(reqSpecial, hasSpecial);

            // Calculate strength
            let strength = 0;
            if (hasLength) strength++;
            if (hasUppercase) strength++;
            if (hasLowercase) strength++;
            if (hasNumber) strength++;
            if (hasSpecial) strength++;

            // Update strength meter if it exists
            if (passwordStrength) {
                updateStrengthMeter(strength);
            }

            // Validate password match
            validatePasswordMatch();

            // Update submit button state
            updateSubmitButton();
        }

        function updateRequirement(element, met) {
            if (met) {
                element.classList.remove('unmet');
                element.classList.add('met');
                element.innerHTML = element.innerHTML.replace('fa-times', 'fa-check');
            } else {
                element.classList.remove('met');
                element.classList.add('unmet');
                element.innerHTML = element.innerHTML.replace('fa-check', 'fa-times');
            }
        }

        function updateStrengthMeter(strength) {
            passwordStrength.className = 'password-strength';
            
            if (strength === 0) {
                passwordStrength.style.display = 'none';
            } else {
                passwordStrength.style.display = 'block';
                if (strength <= 2) {
                    passwordStrength.classList.add('strength-weak');
                } else if (strength === 3) {
                    passwordStrength.classList.add('strength-fair');
                } else if (strength === 4) {
                    passwordStrength.classList.add('strength-good');
                } else {
                    passwordStrength.classList.add('strength-strong');
                }
            }
        }

        function validatePasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';
            const matchElement = document.getElementById('passwordMatch');

            if (!matchElement) return;

            if (confirmPassword === '') {
                matchElement.innerHTML = '';
                return;
            }

            if (password === confirmPassword) {
                matchElement.innerHTML = '<div class="requirement met"><i class="fas fa-check"></i> Passwords match</div>';
            } else {
                matchElement.innerHTML = '<div class="requirement unmet"><i class="fas fa-times"></i> Passwords do not match</div>';
            }
        }

        function updateSubmitButton() {
            if (!submitBtn) return;

            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';
            const termsAccepted = termsCheckbox ? termsCheckbox.checked : true;

            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
            const passwordsMatch = password === confirmPassword && password !== '';

            // If confirm password field doesn't exist, don't require password match
            const isFormValid = hasLength && hasUppercase && hasLowercase && 
                               hasNumber && hasSpecial && 
                               (confirmPasswordInput ? passwordsMatch : true) && 
                               termsAccepted;

            submitBtn.disabled = !isFormValid;
        }

        // Event listeners
        passwordInput.addEventListener('input', validatePassword);
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', validatePasswordMatch);
        }
        if (termsCheckbox) {
            termsCheckbox.addEventListener('change', updateSubmitButton);
        }

        // Initial validation
        validatePassword();
    }

    // Modal functions (only define if needed elsewhere)
    if (typeof openTermsModal === 'undefined') {
        window.openTermsModal = function() {
            const modal = document.getElementById('termsModal');
            if (modal) modal.style.display = 'block';
        };
    }

    if (typeof openPrivacyModal === 'undefined') {
        window.openPrivacyModal = function() {
            const modal = document.getElementById('privacyModal');
            if (modal) modal.style.display = 'block';
        };
    }

    if (typeof closeModal === 'undefined') {
        window.closeModal = function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'none';
        };
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    });
});