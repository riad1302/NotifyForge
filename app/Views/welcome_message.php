<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Push Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-messaging-compat.js"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100 text-gray-800">
<div class="bg-white p-10 rounded shadow max-w-md text-center">
    <h1 class="text-2xl font-bold mb-4">Firebase Push Notification</h1>
    <p id="status" class="text-sm text-gray-600">Checking notification permissions...</p>
    <pre id="token" class="mt-2 text-xs text-left break-all whitespace-pre-wrap hidden bg-gray-200 p-2 rounded"></pre>
</div>

<script>
    const firebaseConfig = {
        apiKey: "AIzaSyCYQl7f__J3Rte44CcoF3AP9bYtSdm-mco",
        authDomain: "laravel-push-b35c9.firebaseapp.com",
        projectId: "laravel-push-b35c9",
        storageBucket: "laravel-push-b35c9.firebasestorage.app",
        messagingSenderId: "791411051535",
        appId: "1:791411051535:web:d816131da22e849d0f277a",
        measurementId: "G-K5SDYJWR2G"
    };

    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    const status = document.getElementById('status');
    const tokenDisplay = document.getElementById('token');

    async function getAndSendToken() {
        try {
            const permission = await Notification.requestPermission();

            if (permission !== 'granted') {
                status.textContent = 'Notification permission denied.';
                return;
            }

            const token = await messaging.getToken({
                vapidKey: "BCYqr3JWvqQg9tm2qTMk034I8fNzX7TogbxZMt9IJ5DIOAi-zpwxK5_SXjpkuoDqdgU6H6HqvTl5G2aE-3lEwbY"
            });

            if (token) {
                status.textContent = 'Token generated successfully.';
                tokenDisplay.textContent = token;
                tokenDisplay.classList.remove('hidden');

                // 🔁 Send token to your backend
                await fetch('/save-token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ token: token })
                });

            } else {
                status.textContent = 'Failed to retrieve token.';
            }
        } catch (err) {
            console.error(err);
            status.textContent = 'Error getting token.';
        }
    }

    // Run immediately on page load
    window.onload = () => {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/firebase-messaging-sw.js')
                .then((reg) => {
                    console.log('Service worker registered:', reg.scope);
                    getAndSendToken(); // ✅ Fetch token after service worker is ready
                })
                .catch((err) => {
                    console.error('Service worker registration failed:', err);
                    status.textContent = 'Service worker failed.';
                });
        } else {
            status.textContent = 'Service worker not supported.';
        }
    };

    // Handle foreground messages
    messaging.onMessage((payload) => {
        console.log('Message received:', payload);
        const title = payload.notification?.title || 'Notification';
        const body = payload.notification?.body || 'You have a new message.';
        if (document.visibilityState === 'hidden' && Notification.permission === 'granted') {
            new Notification(title, { body });
        } else {
            alert(`Foreground message: ${title}`);
        }
    });
</script>
</body>
</html>
