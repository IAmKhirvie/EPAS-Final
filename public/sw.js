// EPAS-E Service Worker
// Update CACHE_VERSION when deploying new assets to bust the cache
const CACHE_VERSION = '2026-04-21-v10';
const CACHE_NAME = `epas-e-${CACHE_VERSION}`;
const OFFLINE_URL = '/offline.html';

// Install event - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(STATIC_ASSETS).catch(err => {
                });
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches but DON'T force reload
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== CACHE_NAME)
                    .map(name => {
                        return caches.delete(name);
                    })
            );
        }).then(() => {
            // Take control of all pages immediately without reloading
            return self.clients.claim();
        })
    );
});

// Static assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/offline.html',
    '/images/logo.png'
];

// Cache strategies — static assets (images, fonts only)
const CACHE_FIRST_PATTERNS = [
    /\/images\//,
    /\/fonts\//,
    /\.woff2?$/,
    /\.ttf$/,
    /\.otf$/,
    /\.png$/,
    /\.jpg$/,
    /\.jpeg$/,
    /\.gif$/,
    /\.svg$/,
    /\.ico$/
];

// CSS, JS, and dynamic content — always check network first
const NETWORK_FIRST_PATTERNS = [
    /\.css$/,
    /\.js$/,
    /\/api\//,
    /\/dashboard/,
    /\/courses/,
    /\/modules/,
    /\/grades/
];

// Install event - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(STATIC_ASSETS).catch(err => {
                });
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches and take control immediately
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== CACHE_NAME)
                    .map(name => {
                        return caches.delete(name);
                    })
            );
        }).then(() => {
            // Take control of all pages immediately
            return self.clients.claim();
        })
    );
});

// Fetch event - handle requests
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip external requests
    if (url.origin !== location.origin) {
        return;
    }

    // Never cache HTML navigation requests — they may contain flash messages
    if (request.mode === 'navigate') {
        return;
    }

    // Skip auth, admin, and private routes - don't cache these
    if (url.pathname.startsWith('/private/') ||
        url.pathname.startsWith('/admin/') ||
        url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/register') ||
        url.pathname.startsWith('/logout') ||
        url.pathname.startsWith('/verify') ||
        url.pathname.startsWith('/email/') ||
        url.pathname.startsWith('/password') ||
        url.pathname.startsWith('/settings')) {
        // Just fetch, don't cache auth routes at all
        return;
    }

    // Check if this is a cache-first pattern (static assets)
    if (CACHE_FIRST_PATTERNS.some(pattern => pattern.test(url.pathname))) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Check if this is a network-first pattern (dynamic content)
    if (NETWORK_FIRST_PATTERNS.some(pattern => pattern.test(url.pathname))) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Default: stale-while-revalidate for other content
    event.respondWith(staleWhileRevalidate(request));
});

// Cache-first strategy
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok && !response.redirected && response.type === 'basic') {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
            trimCache(CACHE_NAME, 100);
        }
        return response;
    } catch (error) {
        return new Response('Offline', { status: 503 });
    }
}

// Network-first strategy
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        // Only cache successful, non-redirected responses
        if (response.ok && !response.redirected && response.type === 'basic') {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }

        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            const offlinePage = await caches.match(OFFLINE_URL);
            if (offlinePage) {
                return offlinePage;
            }
        }

        return new Response('You are offline', { status: 503 });
    }
}

// Stale-while-revalidate strategy (no dedup — avoids Response body lock errors)
async function staleWhileRevalidate(request) {
    const cached = await caches.match(request);

    const networkFetch = fetch(request)
        .then(response => {
            if (response.ok && !response.redirected && response.type === 'basic') {
                const clone = response.clone();
                caches.open(CACHE_NAME).then(cache => {
                    cache.put(request, clone).catch(() => {});
                });
            }
            return response;
        })
        .catch(() => null);

    return cached || await networkFetch || new Response('Offline', { status: 503 });
}

// Trim cache to prevent unbounded growth
async function trimCache(name, maxItems) {
    var cache = await caches.open(name);
    var keys = await cache.keys();
    if (keys.length > maxItems) {
        await cache.delete(keys[0]);
        return trimCache(name, maxItems);
    }
}

// Handle messages from the main thread
self.addEventListener('message', event => {
    // Handle skip waiting to activate new service worker immediately
    if (event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
        return;
    }

    if (event.data.type === 'CACHE_MODULE') {
        const { moduleId, urls } = event.data;
        cacheModule(moduleId, urls).then(() => {
            event.ports[0].postMessage({ success: true, moduleId });
        }).catch(error => {
            event.ports[0].postMessage({ success: false, error: error.message });
        });
    }

    if (event.data.type === 'CLEAR_MODULE_CACHE') {
        const { moduleId } = event.data;
        clearModuleCache(moduleId).then(() => {
            event.ports[0].postMessage({ success: true });
        });
    }

    if (event.data.type === 'GET_CACHED_MODULES') {
        getCachedModules().then(modules => {
            event.ports[0].postMessage({ modules });
        });
    }
});

// Cache a module for offline access
async function cacheModule(moduleId, urls) {
    const cache = await caches.open(`module-${moduleId}`);
    const failures = [];

    for (const url of urls) {
        try {
            const response = await fetch(url);
            if (response.ok) {
                await cache.put(url, response);
            }
        } catch (error) {
            failures.push(url);
        }
    }

    // Store module metadata
    const metadata = {
        moduleId,
        cachedAt: new Date().toISOString(),
        urlCount: urls.length - failures.length,
        failures: failures.length
    };

    await cache.put(
        new Request(`/module-${moduleId}-metadata`),
        new Response(JSON.stringify(metadata))
    );

    return metadata;
}

// Clear a module's cache
async function clearModuleCache(moduleId) {
    await caches.delete(`module-${moduleId}`);
}

// Get list of cached modules
async function getCachedModules() {
    const cacheNames = await caches.keys();
    const modulesCaches = cacheNames.filter(name => name.startsWith('module-'));
    const modules = [];

    for (const cacheName of modulesCaches) {
        const cache = await caches.open(cacheName);
        const metadataResponse = await cache.match(`/${cacheName}-metadata`);
        if (metadataResponse) {
            modules.push(await metadataResponse.json());
        }
    }

    return modules;
}
