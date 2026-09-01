const swUrl = '/sw.js';

// Register service worker
async function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return null;
    }

    try {
        const registration = await navigator.serviceWorker.register(swUrl);
        console.log('Service Worker registered');
        return registration;
    } catch (error) {
        console.error('Failed to register service worker:', error);
        return null;
    }
}

// Request notification permission
async function requestPermission() {
    if (!('Notification' in window)) {
        return null;
    }

    const permission = await Notification.requestPermission();
    return permission;
}

// Get VAPID public key from config
function getVapidPublicKey() {
    const base64Key = window.VAPID_PUBLIC_KEY || '';
    if (!base64Key) {
        return null;
    }

    // Convert base64 to URL-safe base64
    let base64Url = base64Key.replace(/_/g, '/').replace(/-/g, '+');
    while (base64Url.length % 4 !== 0) {
        base64Url += '=';
    }

    return base64Url;
}

// Subscribe to push notifications
async function subscribeToPush(registration) {
    const publicKey = getVapidPublicKey();
    if (!publicKey) {
        console.warn('VAPID public key not configured');
        return;
    }

    try {
        // Convert base64 key to Uint8Array
        const keyLength = publicKey.length;
        const uint8Key = new Uint8Array(keyLength);
        for (let i = 0; i < keyLength; i++) {
            uint8Key[i] = publicKey.charCodeAt(i);
        }

        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: uint8Key,
        });

        // Send subscription to backend
        await fetch('/api/push-subscriptions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                p256dh: arrayBufferToBase64(subscription.getKey('p256dh')),
                auth: arrayBufferToBase64(subscription.getKey('auth')),
            }),
        });

        console.log('Push subscription created');
    } catch (error) {
        console.error('Failed to subscribe to push:', error);
    }
}

// Unsubscribe from push notifications
async function unsubscribeFromPush(registration) {
    const subscription = await registration.pushManager.getSubscription();

    if (subscription) {
        await subscription.unsubscribe();

        // Send deletion to backend
        await fetch(`/api/push-subscriptions/${encodeURIComponent(subscription.endpoint)}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        console.log('Push subscription deleted');
    }
}

// Convert ArrayBuffer to Base64
function arrayBufferToBase64(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.byteLength; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return btoa(binary);
}

// Initialize
async function initPushNotifications() {
    const registration = await registerServiceWorker();

    if (!registration) {
        return;
    }

    const permission = await requestPermission();

    if (permission === 'granted') {
        await subscribeToPush(registration);
    }
}

// Export functions for use in other scripts
window.PushNotifications = {
    init: initPushNotifications,
    subscribe: subscribeToPush,
    unsubscribe: unsubscribeFromPush,
};

// Start initialization
initPushNotifications();