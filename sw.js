const CACHE_NAME = 'puta-app-v2';
const STATIC_CACHE = 'puta-static-v1';
const RUNTIME_CACHE = 'puta-runtime-v1';

// Core app shell assets to cache during install
const CORE_ASSETS = [
  '/puta/index.php',
  '/puta/style.css',
  '/puta/login.php',
  '/puta/signup.php',
  '/puta/offline.html'
];

// Utility: is this a navigation request?
function isNavigationRequest(req) {
  return req.mode === 'navigate' || (req.method === 'GET' && req.headers.get('accept') && req.headers.get('accept').includes('text/html'));
}

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) => cache.addAll(CORE_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.filter(k => k !== STATIC_CACHE && k !== RUNTIME_CACHE).map(k => caches.delete(k))
    ))
  );
  self.clients.claim();
});

// Simple caching strategy:
// - Navigation (HTML) => network-first, fallback to cache/offline
// - Static assets (CSS/JS/images) => cache-first (stale-while-revalidate)
// - API / PHP endpoints => network-first, fallback to cache if available
self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Only handle same-origin requests to avoid CORS surprises
  if (url.origin !== location.origin) return;

  // Navigation requests (HTML)
  if (isNavigationRequest(req)) {
    event.respondWith(
      fetch(req).then((res) => {
        // Update runtime cache for navigations optionally
        return res;
      }).catch(() => caches.match('/puta/offline.html'))
    );
    return;
  }

  // Don't interfere with POST/PUT/DELETE
  if (req.method !== 'GET') return;

  // Static assets: cache-first, then revalidate in background
  if (req.destination === 'style' || req.destination === 'script' || req.destination === 'image' || req.url.endsWith('.css') || req.url.endsWith('.js') || req.url.endsWith('.png') || req.url.endsWith('.jpg') || req.url.endsWith('.jpeg')) {
    event.respondWith(
      caches.match(req).then((cached) => {
        const networkFetch = fetch(req).then((res) => {
          // Update static cache in background
          caches.open(STATIC_CACHE).then((cache) => cache.put(req, res.clone()));
          return res.clone();
        }).catch(() => null);
        return cached || networkFetch.then(r => r) || caches.match('/puta/offline.html');
      })
    );
    return;
  }

  // API or PHP endpoints => network-first, fallback to runtime cache
  if (req.url.includes('.php') || req.url.includes('/api/') || req.headers.get('accept') && req.headers.get('accept').includes('application/json')) {
    event.respondWith(
      fetch(req).then((res) => {
        // cache response for offline fallback
        const copy = res.clone();
        caches.open(RUNTIME_CACHE).then((cache) => cache.put(req, copy));
        return res;
      }).catch(() => caches.match(req).then(cached => cached || caches.match('/puta/offline.html')))
    );
    return;
  }

  // Default: try cache, then network
  event.respondWith(
    caches.match(req).then((cached) => cached || fetch(req).catch(() => caches.match('/puta/offline.html')))
  );
});
