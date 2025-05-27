<?php

namespace App\Commands;

use App\Models\User;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class SendUserNotificationCommand extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'push:notify';
    protected $description = 'Send a Firebase notification to all users';
    protected $usage       = 'push:notify "Title" "Message"';
    protected $arguments   = [
        'title'   => 'Notification title',
        'message' => 'Notification message body'
    ];

    public function run(array $params)
    {
        $title = $params[0] ?? null;
        $message = $params[1] ?? null;

        if (!$title || !$message) {
            CLI::error("Both title and message are required.");
            CLI::write("Usage: php spark push:notify \"Title\" \"Message\"", 'yellow');
            return;
        }

        $redis = getRedis();
        $userModel = new User();
        $users = $userModel->where('device_token !=', '')->findAll();

        if (empty($users)) {
            CLI::write("No users with device tokens found.", 'red');
            return;
        }

        // Push all user tokens to Redis queue
        $redis->del('user_list_queue');
        foreach ($users as $user) {
            $redis->rPush('user_list_queue', json_encode([
                'id' => $user['id'],
                'device_token' => $user['device_token'],
            ]));
        }

        CLI::write("Pushed " . count($users) . " users to Redis queue.", 'green');

        // Send push notifications
        $notificationService = Services::fireBaseDBNotificationService();
        $result = $notificationService->sendNotification($title, $message);

        CLI::write("Notification sent: {$result['success']} |  {$result['fail']}", 'cyan');
    }
}
