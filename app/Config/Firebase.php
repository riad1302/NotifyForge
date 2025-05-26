<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Firebase extends BaseConfig
{
    public string $serverKey;

    public function __construct()
    {
        $this->serverKey = getenv('FIREBASE_SERVER_KEY') ?: '';
    }
}
