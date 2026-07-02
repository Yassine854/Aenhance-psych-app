const CACHE_NAME = 'aenhance-pwa-v2';
const ASSETS_TO_CACHE = [
  '/favicon.ico'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS_TO_CACHE))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.map((key) => {
        if (key !== CACHE_NAME) return caches.delete(key);
      })
    ))
  );
  self.clients.claim();
});

function shouldBypassCache(request, url) {
  if (request.method !== 'GET') return true;
  if (url.origin !== self.location.origin) return true;
  if (request.mode === 'navigate') return true;

  const pathname = url.pathname;
  if (
    pathname.startsWith('/notifications') ||
    pathname.startsWith('/login') ||
    pathname.startsWith('/logout') ||
    pathname.startsWith('/dashboard') ||
    pathname.startsWith('/patient') ||
    pathname.startsWith('/psychologist') ||
    pathname.startsWith('/admin')
  ) {
    return true;
  }

  const accept = request.headers.get('Accept') || '';
  if (accept.includes('text/html') || accept.includes('application/json')) {
    return true;
  }

  return false;
}

async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;

  const response = await fetch(request);
  if (response && response.ok) {
    const cache = await caches.open(CACHE_NAME);
    cache.put(request, response.clone());
  }

  return response;
}

async function networkFirst(request) {
  try {
    return await fetch(request);
  } catch (error) {
    const cached = await caches.match(request);
    if (cached) return cached;
    throw error;
  }
}

self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  if (shouldBypassCache(request, url)) {
    event.respondWith(networkFirst(request));
    return;
  }

  const cacheableDestinations = ['style', 'script', 'worker', 'image', 'font'];
  if (!cacheableDestinations.includes(request.destination)) {
    event.respondWith(networkFirst(request));
    return;
  }

  event.respondWith(cacheFirst(request));
});
