<?php

namespace App\Services;

use App\Interface\NotificationInterface;
use CodeIgniter\CLI\CLI;
use Config\Firebase;
use Google\Client;

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

        $credentialsFilePath = FCPATH . 'firebase.json';

        $client = new Client();


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

                $client->setAuthConfig($credentialsFilePath);

                $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                $client->refreshTokenWithAssertion();
                $token = $client->getAccessToken();

                $access_token = $token['access_token'];

                // Set up the HTTP headers
                $headers = [
                    "Authorization: Bearer $access_token",
                    'Content-Type: application/json'
                ];

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
                //curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);


                if ($httpCode === 200) {
                    $success++;
//                    $redis->rPush('notification_sse_queue', json_encode([
//                        'title' => $title,
//                        'body' => $body,
//                    ]));
                } else {
                    $fail++;
                    CLI::error("Failed to send to: {$user['device_token']} | HTTP Code: $httpCode | cURL Error: $curlError");
                }
            }

            //CLI::write("Notifications sent to $success users | Failed for $fail", 'yellow');

        } catch (\Throwable $e) {
            CLI::error("An error occurred while sending notifications: " . $e->getMessage());
            log_message('error', 'Firebase notification error: ' . $e->getMessage());
        }

        return ['success' => $success, 'fail' => $fail];
    }
}
