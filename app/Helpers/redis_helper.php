<?php

use Config\Cache;

function getRedis(): \Redis
{
    $config = new Cache();

    $redis = new \Redis();
    $redis->connect($config->redis['host'], $config->redis['port']);

    if (!empty($config->redis['password'])) {
        $redis->auth($config->redis['password']);
    }

    if (!empty($config->redis['database'])) {
        $redis->select($config->redis['database']);
    }

    return $redis;
}
