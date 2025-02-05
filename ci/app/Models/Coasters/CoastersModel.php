<?php

declare(strict_types=1);

namespace App\Models\Coasters;

use App\Dto\CoasterDto;
use App\Libraries\ClueRedisClient\RedisClientFactory;
use Clue\React\Redis\RedisClient;

readonly class CoastersModel
{
    protected RedisClient $redisClient;

    public function __construct()
    {
        $this->redisClient = RedisClientFactory::getInstance();
    }

    public function saveCoaster(CoasterDto $coasterDto): void
    {
        $this->redisClient->set("c:$coasterDto->coasterId", serialize($coasterDto));
    }
}