<?php

namespace App\Commands;

use App\Interface\NotificationInterface;
use App\Models\User;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Cache;
use Config\Services;
use Faker\Factory;

class BulkUserPushCommand extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'push:bulk';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Insert 12k users, push to Redis, and send Firebase push notification';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $model = new User();
        $redis = getRedis();
        $faker = Factory::create();

        $batchSize = 1000;
        $dataBatch = [];

        $redis->del('user_queue');

        for ($i = 1; $i <= 12000; $i++) {
            $user = [
                'name'  => $faker->name,
                'email' => $faker->safeEmail,
                'device_token' => $faker->uuid(),
            ];

            $dataBatch[] = $user;

            if (count($dataBatch) >= $batchSize) {
                $model->insertBatchAndPushToRedis($dataBatch);
                CLI::write("Inserted & pushed {$i} users", 'green');
                $dataBatch = [];
            }
        }

        if (!empty($dataBatch)) {
            $model->insertBatchAndPushToRedis($dataBatch);
        }

        $notificationService = Services::fireBaseNotificationService();

        $result = $notificationService->sendNotification(
            "Welcome to Our App 🎉",
            "Thanks for joining our community — we're excited to have you here!"
        );

        if ($result['fail'] === 0) {
            CLI::write("All done. Firebase notified {$result['success']} users successfully.", 'green');
        } else {
            CLI::write("Notification finished with issues. Success: {$result['success']}, Failed: {$result['fail']}", 'yellow');
        }
    }
}
