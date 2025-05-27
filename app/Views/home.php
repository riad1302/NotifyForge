<!DOCTYPE html>
<html>
<head>
    <title>Desktop Notification Test</title>
</head>
<body>
<button onclick="showNotification()">Test Notification</button>

<script>
    async function showNotification() {
        // if (Notification.permission !== 'granted') {
        //     console.log('not granted')
        //     await Notification.requestPermission();
        // }

        if (Notification.permission === 'granted') {
            console.log('granted')
            new Notification("Test Title", {
                body: "This is a desktop notification from JavaScript.",
            });
        } else {
            alert('Permission not granted.');
        }
    }
</script>
</body>
</html>
