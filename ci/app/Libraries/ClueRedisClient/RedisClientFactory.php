<?php

declare(strict_types=1);

namespace App\Libraries\ClueRedisClient;

use Clue\React\Redis\RedisClient;

final class RedisClientFactory
{
    public static function getInstance(): RedisClient
    {
        return new RedisClient('redis://:' . env('REDIS_PASSWORD') . '@' . env('REDIS_HOST') . ':' . env('REDIS_PORT') . '?timeout=1');
    }
}