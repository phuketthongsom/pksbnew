const CACHE = 'pksb-v2';
const PRECACHE = [
  '/',
  '/timetable.php',
  '/tracking.php',
  '/payment.php',
  '/attractions.php',
  '/assets/css/style.css',
  '/assets/images/logo.png',
  '/assets/images/icon-192.png'
];

// Install — precache core assets
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE).then(c => c.addAll(PRECACHE).catch(() => {}))
  );
  self.skipWaiting();
});

// Activate — purge old caches
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Fetch — network-first, fall back to cache
self.addEventListener('fetch', e => {
  if (e.request.method !== 'GET') return;
  // Skip API and admin requests
  const url = new URL(e.request.url);
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/admin/')) return;

  e.respondWith(
    fetch(e.request)
      .then(r => {
        if (r.ok) {
          const clone = r.clone();
          caches.open(CACHE).then(c => c.put(e.request, clone));
        }
        return r;
      })
      .catch(() => caches.match(e.request))
  );
});

// Push — show notification
self.addEventListener('push', e => {
  let data = { title: 'Phuket Smart Bus', body: '', url: '/' };
  try { data = { ...data, ...e.data.json() }; } catch {}

  e.waitUntil(
    self.registration.showNotification(data.title, {
      body: data.body,
      icon: '/assets/images/icon-192.png',
      badge: '/assets/images/icon-192.png',
      tag: 'pksb-push',
      renotify: true,
      data: { url: data.url },
      vibrate: [200, 100, 200],
      actions: [
        { action: 'open', title: 'เปิด' },
        { action: 'close', title: 'ปิด' }
      ]
    })
  );
});

// Notification click — open the linked URL
self.addEventListener('notificationclick', e => {
  e.notification.close();
  if (e.action === 'close') return;
  const url = e.notification.data?.url || '/';
  e.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(list => {
      for (const c of list) {
        if (c.url.includes(self.location.origin) && 'focus' in c) {
          c.navigate(url);
          return c.focus();
        }
      }
      return clients.openWindow(url);
    })
  );
});
