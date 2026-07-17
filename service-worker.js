const CACHE = 'fwsp-offline-v1';
const SHELL = [
  './', './index.php', './index.php?page=individual-delivery', './index.php?page=organization-delivery',
  './assets/js/app.js', './assets/css/style.css', './assets/images/nfa-website-banner.png', './favicon.ico',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js'
];
self.addEventListener('install', event => event.waitUntil(caches.open(CACHE).then(cache => cache.addAll(SHELL.map(url => new Request(url, { mode: 'no-cors' }))))));
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));
self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  const url = new URL(event.request.url);
  const isPage = url.origin === self.location.origin && url.pathname.endsWith('/index.php');
  const allowedOfflinePage = !isPage || !url.searchParams.has('page') || ['individual-delivery', 'organization-delivery'].includes(url.searchParams.get('page'));
  event.respondWith(fetch(event.request).then(response => {
    const copy = response.clone();
    if (allowedOfflinePage && (response.ok || response.type === 'opaque')) caches.open(CACHE).then(cache => cache.put(event.request, copy));
    return response;
  }).catch(() => {
    if (!allowedOfflinePage) return new Response('This feature is unavailable while offline.', { status: 503, headers: { 'Content-Type': 'text/plain' } });
    return caches.match(event.request).then(hit => hit || caches.match('./index.php'));
  }));
});
