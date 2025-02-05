<?php

declare(strict_types=1);

namespace App\Libraries\ClueRedisClient;

use Clue\React\Redis\RedisClient;

final class RedisClientFactory
{
    public static function getInstance(): RedisClient
    {
        return new RedisClient('redis://:' . getenv('REDIS_PASSWORD') . '@' . getenv('REDIS_HOST') . ':' . getenv('REDIS_PORT') . '?timeout=1');
    }
}