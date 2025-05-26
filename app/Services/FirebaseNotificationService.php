<?php

namespace App\Services;

use App\Interface\NotificationInterface;
use CodeIgniter\CLI\CLI;
use Config\Firebase;

class FirebaseNotificationService implements NotificationInterface
{
    protected string $serverKey;

    public function __construct()
    {
        // Get server key from env or config
        $firebaseConfig = new Firebase();
        $this->serverKey = $firebaseConfig->serverKey;
    }

    public function sendNotification(string $title, string $body): array
    {
        $redis = getRedis();
        $queueKey = 'user_queue';
        $success = 0;
        $fail = 0;

        try {
            $queueLength = $redis->llen($queueKey);

            if (empty($this->serverKey)) {
                throw new \InvalidArgumentException('Firebase server key is not configured.');
            }

            for ($i = 0; $i < $queueLength; $i++) {
                $userJson = $redis->lindex($queueKey, $i);
                $user = json_decode($userJson, true);

                if (empty($user['device_token'])) {
                    continue;
                }

                $data = [
                    'to' => $user['device_token'],
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                ];

                $ch = curl_init('https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: key=' . $this->serverKey,
                    'Content-Type: application/json',
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if ($httpCode === 200) {
                    $success++;
                } else {
                    $fail++;
                    CLI::error("Failed to send to: {$user['device_token']} | HTTP Code: $httpCode | cURL Error: $curlError");
                }
            }

            CLI::write("Notifications sent to $success users | Failed for $fail", 'yellow');

        } catch (\Throwable $e) {
            CLI::error("An error occurred while sending notifications: " . $e->getMessage());
            log_message('error', 'Firebase notification error: ' . $e->getMessage());
        }

        return ['success' => $success, 'fail' => $fail];
    }
}
