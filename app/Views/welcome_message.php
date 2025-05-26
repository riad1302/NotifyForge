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
    <button id="enableNotifications" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 font-semibold">Enable Notifications</button>
    <p id="status" class="mt-4 text-sm text-gray-600">Awaiting user action...</p>
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
    const enableBtn = document.getElementById('enableNotifications');

    enableBtn.addEventListener('click', async () => {
        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                status.textContent = 'Permission denied.';
                return;
            }

            const token = await messaging.getToken({
                vapidKey: "BCYqr3JWvqQg9tm2qTMk034I8fNzX7TogbxZMt9IJ5DIOAi-zpwxK5_SXjpkuoDqdgU6H6HqvTl5G2aE-3lEwbY" // Replace with your Web Push key from Firebase
            });

            if (token) {
                tokenDisplay.textContent = token;
                tokenDisplay.classList.remove('hidden');
                status.textContent = "Token generated successfully.";
                // Optionally send token to backend using fetch/ajax
            } else {
                status.textContent = 'Failed to retrieve token.';
            }
        } catch (e) {
            console.error(e);
            status.textContent = 'An error occurred.';
        }
    });

    messaging.onMessage((payload) => {
        console.log('Message received:', payload);
        alert(`Foreground message: ${payload.notification.title}`);
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/firebase-messaging-sw.js')
            .then((reg) => {
                console.log('Service worker registered:', reg.scope);
            })
            .catch((err) => {
                console.error('Service worker registration failed:', err);
            });
    }
</script>
</body>
</html>
