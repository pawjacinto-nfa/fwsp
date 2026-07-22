const CACHE = 'fwsp-offline-v2';
const SHELL = [
  './', './index.php', './index.php?page=individual-delivery', './index.php?page=organization-delivery',
  './assets/js/app.js', './assets/css/style.css', './assets/images/nfa-website-banner.png', './favicon.ico',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js'
];
self.addEventListener('install', event => event.waitUntil(self.skipWaiting()));
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));
self.addEventListener('message', event => {
  if (event.data?.type !== 'FWSP_INSTALL_OFFLINE') return;
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
      client?.postMessage({ type: 'FWSP_INSTALL_ERROR', resource: url, message: 'Unable to download a required offline resource.' });
      return;
    }
    client?.postMessage({ type: 'FWSP_INSTALL_PROGRESS', completed: index + 1, total, percent: Math.round(((index + 1) / total) * 100), resource: url });
  }
  client?.postMessage({ type: 'FWSP_INSTALL_COMPLETE', percent: 100 });
}
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
