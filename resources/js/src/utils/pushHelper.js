import axios from 'axios';

export async function subscribeToPushNotifications() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.warn('Push messaging is not supported.');
        return;
    }

    try {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            console.log('Push notification permission denied.');
            return;
        }

        const registration = await navigator.serviceWorker.ready;
        
        // Hardcoded VAPID public key
        const vapidPublicKey = "BNL8SPt8_9sORZHJcK0gtYtTSzwNaA-orLS3ePFP_Ai5F5e_GdJ5f18O6VuoOi_1IyRGhRHzcXDPrBTExOA38WA";
        if (!vapidPublicKey) {
            console.error('VAPID public key not found');
            return;
        }

        const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: convertedVapidKey
        });

        // Send to backend
        await axios.post('/push-subscribe', subscription.toJSON());
        console.log('Push subscription successful');
    } catch (error) {
        console.error('Error during push subscription', error);
    }
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}
