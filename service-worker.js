const CACHE = 'fsr-offline-v3';
const SHELL = [
  './', './index.php', './index.php?page=encode-farmer', './index.php?page=individual-delivery', './index.php?page=organization-delivery',
  './index.php?page=farmer-organization-library', './index.php?page=delivery-schedules',
  './assets/js/app.js', './assets/css/style.css', './assets/css/delivery-schedule-print.css', './assets/images/nfa-website-banner.png', './favicon.ico',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js'
];
self.addEventListener('install', event => event.waitUntil(self.skipWaiting()));
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));
self.addEventListener('message', event => {
  if (event.data?.type !== 'FSR_INSTALL_OFFLINE') return;
  event.waitUntil(cacheOfflineWorkspace(event.source));
});

async function cacheOfflineWorkspace(client) {
  const cache = await caches.open(CACHE);
  const total = SHELL.length;
  for (let index = 0; index < total; index += 1) {
    const url = SHELL[index];
    try {
      const request = new Request(url, { mode: url.startsWith('http') ? 'no-cors' : 'same-origin' });
      const response = await fetch(request);
      if (!response.ok && response.type !== 'opaque') {
        throw new Error('Bad offline resource response.');
      }
      await cache.put(request, response);
    } catch (error) {
      client?.postMessage({ type: 'FSR_INSTALL_ERROR', resource: url, message: 'Unable to download a required offline resource.' });
      return;
    }
    client?.postMessage({ type: 'FSR_INSTALL_PROGRESS', completed: index + 1, total, percent: Math.round(((index + 1) / total) * 100), resource: url });
  }
  client?.postMessage({ type: 'FSR_INSTALL_COMPLETE', percent: 100 });
}
self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  const url = new URL(event.request.url);
  const isAppRequest = url.origin === self.location.origin;
  event.respondWith(fetch(event.request).then(response => {
    const copy = response.clone();
    if (isAppRequest && (response.ok || response.type === 'opaque')) caches.open(CACHE).then(cache => cache.put(event.request, copy));
    return response;
  }).catch(() => {
    if (!isAppRequest) return new Response('This feature is unavailable while offline.', { status: 503, headers: { 'Content-Type': 'text/plain' } });
    return caches.match(event.request).then(hit => hit || caches.match('./index.php'));
  }));
});
