<?php

declare(strict_types=1);

namespace App\Models\Wagons;

use App\Dto\WagonDto;
use App\Libraries\ClueRedisClient\RedisClientFactory;
use Clue\React\Redis\RedisClient;

readonly class WagonsModel
{
    protected RedisClient $redisClient;

    public function __construct()
    {
        $this->redisClient = RedisClientFactory::getInstance();
    }

    public function saveWagon(WagonDto $wagonDto): void
    {
        $this->redisClient->set("c:$wagonDto->coasterId:$wagonDto->wagonId", serialize($wagonDto));
    }

    public function deleteWagon(string $coasterId, string $wagonId): void
    {
        $this->redisClient->del("c:$coasterId:$wagonId");
    }
}