<?php

namespace App\Services;

use App\Interface\NotificationInterface;
use App\Models\User;
use CodeIgniter\CLI\CLI;
use Config\Firebase;
use Google\Client;

class FirebaseDBNotificationService implements NotificationInterface
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
        $success = 0;
        $fail = 0;

        $credentialsFilePath = FCPATH . 'firebase.json';
        $client = new Client();
        $redis = getRedis();
        $queueKey = 'user_list_queue';

        try {
            $queueLength = $redis->llen($queueKey);

            if ($queueLength === 0) {
                CLI::write("Redis queue is empty. No notifications to send.", 'yellow');
                return ['success' => 0, 'fail' => 0];
            }

            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $token = $client->getAccessToken();
            $access_token = $token['access_token'];

            $headers = [
                "Authorization: Bearer $access_token",
                'Content-Type: application/json'
            ];

            for ($i = 0; $i < $queueLength; $i++) {
                $userJson = $redis->lindex($queueKey, $i);
                $user = json_decode($userJson, true);

                if (empty($user['device_token'])) {
                    continue;
                }

                $data = [
                    "message" => [
                        "token" => $user['device_token'],
                        "notification" => [
                            "title" => $title,
                            "body" => $body,
                        ],
                        "apns" => [
                            "payload" => [
                                "aps" => [
                                    "sound" => "default"
                                ]
                            ]
                        ]
                    ]
                ];

                $payload = json_encode($data);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/laravel-push-b35c9/messages:send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                $response = curl_exec($ch);
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

        } catch (\Throwable $e) {
            CLI::error("Error during notification: " . $e->getMessage());
            log_message('error', 'Firebase notification error: ' . $e->getMessage());
        }

        return ['success' => $success, 'fail' => $fail];
    }

}
