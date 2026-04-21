  <!-- PWA Service Worker Registration -->
  <script>
    // Force clear stale SW caches on version mismatch
    (function() {
        var SW_VERSION = '2026-04-21-v10';
        var lastSW = localStorage.getItem('sw_version');
        if (lastSW !== SW_VERSION && 'caches' in window) {
            caches.keys().then(function(names) {
                names.forEach(function(name) { caches.delete(name); });
            });
            localStorage.setItem('sw_version', SW_VERSION);
        }
    })();

    // Service Worker Registration
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' })
            .then(function(registration) {
                console.log('[SW] Service Worker registered:', registration.scope);
                registration.update();
            })
            .catch(function(err) {
                console.log('[SW] Service Worker registration failed:', err);
            });
    }

    // PWA Install Prompt Handler
    if (typeof window.deferredPrompt === 'undefined') window.deferredPrompt = null;
    var deferredPrompt = window.deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        // Show install button if exists
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.display = 'block';
            installBtn.addEventListener('click', () => {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                    }
                    deferredPrompt = null;
                    installBtn.style.display = 'none';
                });
            });
        }
    });

    // Offline/Online status handlers
    window.addEventListener('online', () => {
        document.body.classList.remove('offline-mode');
    });

    window.addEventListener('offline', () => {
        document.body.classList.add('offline-mode');
    });

    // Module caching helper function
    window.cacheModuleForOffline = async function(moduleId, moduleUrl) {
        if (!navigator.serviceWorker.controller) {
            console.error('Service worker not ready');
            return { success: false, error: 'Service worker not ready' };
        }

        return new Promise((resolve) => {
            const messageChannel = new MessageChannel();
            messageChannel.port1.onmessage = (event) => {
                resolve(event.data);
            };

            // Collect URLs to cache for this module
            const urlsToCache = [
                moduleUrl,
                `/modules/${moduleId}`,
                `/modules/${moduleId}/download`
            ];

            navigator.serviceWorker.controller.postMessage(
                { type: 'CACHE_MODULE', moduleId, urls: urlsToCache },
                [messageChannel.port2]
            );
        });
    };

    // Hide page loader after login redirect
    @if(session('show_login_loader'))
    (function() {
        var loader = document.getElementById('page-loader');
        if (loader) {
            // Show loader briefly after login redirect, then hide
            setTimeout(function() {
                loader.classList.add('hidden');
                setTimeout(function() {
                    if (window._circuitAnimation) {
                        cancelAnimationFrame(window._circuitAnimation);
                        delete window._circuitAnimation;
                    }
                    if (loader.parentNode) loader.remove();
                }, 300);
            }, 800); // Show for 800ms to give transition feel
        }
    })();
    @endif
  </script>
