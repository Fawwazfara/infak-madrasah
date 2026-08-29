// Basic Service Worker for PWA Add-to-Home-Screen capability
self.addEventListener('install', event => {
    // Skip waiting so the service worker activates immediately
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    // Claim clients to take control immediately
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', event => {
    // Just pass through all requests for now
    // We only need the SW to exist and be valid to trigger the install prompt
    event.respondWith(fetch(event.request));
});

// Handle push events
self.addEventListener('push', function(event) {
    if (event.data) {
        const data = event.data.json();
        
        const options = {
            body: data.body,
            icon: data.icon || '/icons/icon-192x192.png',
            vibrate: [100, 50, 100],
            data: data.data || { url: '/' },
            actions: data.actions || []
        };

        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

// Handle notification click
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    
    // Open the app to the relevant page
    const urlToOpen = event.notification.data.url || '/';
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            for (let i = 0; i < clientList.length; i++) {
                let client = clientList[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});
