const VERSION = 'nexora-v1';
const ASSETS = [
  '/',
  '/favicon.ico',
  '/admin',
  '/admin/products',
  '/admin/kds',
  '/mobile/shopping-list',
  '/css/app.css',
  '/js/app.js'
];

self.addEventListener('install', function(e) {
  e.waitUntil(
    caches.open(VERSION).then(function(cache) {
      return cache.addAll(ASSETS).catch(function(){});
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', function(e) {
  e.waitUntil(
    caches.keys().then(function(keys) {
      return Promise.all(keys.map(function(k) {
        if (k !== VERSION) return caches.delete(k);
      }));
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', function(e) {
  var req = e.request;
  var url = new URL(req.url);
  if (req.method !== 'GET') return;
  if (url.origin === location.origin) {
    if (url.pathname.startsWith('/css') || url.pathname.startsWith('/js') || url.pathname === '/favicon.ico') {
      e.respondWith(
        caches.match(req).then(function(r) {
          return r || fetch(req).then(function(res) {
            var copy = res.clone();
            caches.open(VERSION).then(function(cache) { cache.put(req, copy); });
            return res;
          });
        })
      );
      return;
    }
    e.respondWith(
      fetch(req).then(function(res) {
        var copy = res.clone();
        caches.open(VERSION).then(function(cache) { cache.put(req, copy); });
        return res;
      }).catch(function() {
        return caches.match(req);
      })
    );
  }
});
