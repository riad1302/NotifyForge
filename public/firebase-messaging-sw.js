importScripts('https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.6.1/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyCYQl7f__J3Rte44CcoF3AP9bYtSdm-mco",
    authDomain: "laravel-push-b35c9.firebaseapp.com",
    projectId: "laravel-push-b35c9",
    storageBucket: "laravel-push-b35c9.firebasestorage.app",
    messagingSenderId: "791411051535",
    appId: "1:791411051535:web:d816131da22e849d0f277a",
    measurementId: "G-K5SDYJWR2G"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
    console.log('[firebase-messaging-sw.js] Received background message:', payload);

    const notificationTitle = payload.notification?.title || 'New Notification';
    const notificationOptions = {
        body: payload.notification?.body || 'You have a new message.',
        icon: '/firebase-logo.png' // Add your own icon here
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow('http://localhost:8080')
    );
});
