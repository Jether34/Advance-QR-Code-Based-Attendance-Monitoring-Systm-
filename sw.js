const CACHE_NAME = 'puta-app-v1';
const CORE_ASSETS = [
  '/',
  '/puta/index.php',
  '/puta/style.css',
  '/puta/login.php',
  '/puta/signup.php',
  '/puta/offline.html'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(CORE_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
    ))
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  // Network-first for navigation requests
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).catch(() => caches.match('/puta/offline.html'))
    );
    return;
  }

  // Cache-first for other requests
  event.respondWith(
    caches.match(req).then((cached) => cached || fetch(req).then((res) => {
      // Optionally cache fetched responses
      return res;
    })).catch(() => caches.match('/puta/offline.html'))
  );
});
