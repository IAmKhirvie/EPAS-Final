// Dropdown functionality for login dropdown
const loginDropdownBtn = document.getElementById('login-dropdown-btn');
const loginDropdown = document.getElementById('login-dropdown');
let loginDropdownOriginalParent = null;

if (loginDropdownBtn && loginDropdown) {
    loginDropdownOriginalParent = loginDropdown.parentElement;

    loginDropdownBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const wasActive = loginDropdown.classList.contains('active');

        if (!wasActive) {
            closeAllDropdowns();

            // On mobile, move dropdown to body so it escapes navbar z-index
            if (window.innerWidth <= 1032) {
                document.body.appendChild(loginDropdown);
                showLoginBackdrop();
            }

            loginDropdown.classList.add('active');
        } else {
            closeAllDropdowns();
        }
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown') && !e.target.closest('.navbar-item')) {
        closeAllDropdowns();
    }
});

function closeAllDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown, .popover');
    dropdowns.forEach(dropdown => {
        dropdown.classList.remove('active');
    });

    // Move login dropdown back to its original parent
    if (loginDropdown && loginDropdownOriginalParent &&
        loginDropdown.parentElement === document.body) {
        loginDropdownOriginalParent.appendChild(loginDropdown);
    }

    hideLoginBackdrop();
}

// Close dropdown when clicking on a dropdown item
document.addEventListener('click', function(e) {
    if (e.target.closest('.dropdown-item')) {
        closeAllDropdowns();
    }
});

// Mobile login modal backdrop
function showLoginBackdrop() {
    let backdrop = document.getElementById('login-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id = 'login-backdrop';
        backdrop.className = 'login-backdrop';
        backdrop.addEventListener('click', function() {
            closeAllDropdowns();
        });
        document.body.appendChild(backdrop);
    }
    requestAnimationFrame(() => backdrop.classList.add('active'));
}

function hideLoginBackdrop() {
    const backdrop = document.getElementById('login-backdrop');
    if (backdrop) {
        backdrop.classList.remove('active');
        setTimeout(() => backdrop.remove(), 300);
    }
}
