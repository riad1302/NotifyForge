<?php

namespace App\Interface;

interface NotificationInterface
{
    public function sendNotification(string $title, string $body): array;
}