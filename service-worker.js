// Cache public static assets only. Never persist authenticated HTML or uploaded records.
const CACHE = 'fsr-public-static-v4';
const staticResource = request => {
  const url = new URL(request.url);
  const base = new URL('./', self.location.href).pathname;
  return request.method === 'GET' && url.origin === self.location.origin
    && url.pathname.startsWith(base + 'assets/')
    && /^assets\/(?:css|js|images)\/[^?]+\.(?:css|js|png|jpe?g|webp|ico|woff2?)$/i.test(url.pathname.slice(base.length));
};
self.addEventListener('install', event => event.waitUntil(self.skipWaiting()));
self.addEventListener('activate', event => event.waitUntil((async () => {
  for (const name of await caches.keys()) {
    if (name.startsWith('fsr-') && name !== CACHE) await caches.delete(name);
  }
  await self.clients.claim();
})()));
self.addEventListener('message', event => {
  if (event.data?.type === 'FSR_INSTALL_OFFLINE') {
    event.source?.postMessage({ type: 'FSR_INSTALL_ERROR', resource: 'offline pages', message: 'Offline pages are disabled to protect personal records. Reconnect to continue working.' });
  }
});
self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  if (!staticResource(event.request)) {
    event.respondWith(fetch(event.request).catch(() => new Response('This page requires a connection. Reconnect to continue.', {
      status: 503, headers: { 'Content-Type': 'text/plain', 'Cache-Control': 'no-store' }
    })));
    return;
  }
  event.respondWith((async () => {
    const cache = await caches.open(CACHE);
    try {
      const response = await fetch(event.request);
      if (response.ok) await cache.put(event.request, response.clone());
      return response;
    } catch {
      return await cache.match(event.request) || new Response('Offline', { status: 503 });
    }
  })());
});
