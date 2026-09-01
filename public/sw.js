self.addEventListener('push', function(event) {
    const data = event.data ? event.data.json() : {};

    event.waitUntil(
        self.registration.showNotification(data.title || 'YourShout', {
            body: data.body || 'You have a new notification',
            icon: '/favicon.png',
            badge: '/favicon.png',
            data: data.url ? { url: data.url } : undefined,
        })
    );
});

self.addEventListener('notificationclick', function(event) {
    event.waitUntil(
        clients.openWindow(event.notification.data?.url || '/')
    );
});

self.addEventListener('notificationclose', function(event) {
    // Handle notification dismissal if needed
});