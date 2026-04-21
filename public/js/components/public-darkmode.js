/**
 * Public Dark Mode Handler
 * Delegates to shared utils/dark-mode.js (must be loaded first).
 */
document.addEventListener('DOMContentLoaded', function () {
    if (window.initDarkMode) window.initDarkMode();
});
