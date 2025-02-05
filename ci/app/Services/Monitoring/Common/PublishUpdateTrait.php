<?php

declare(strict_types=1);

namespace App\Services\Monitoring\Common;

use App\Services\Monitoring\CoasterMonitoringService;

trait PublishUpdateTrait
{
    public function publishUpdate(): void
    {
        $this->redisClient->keys('c:*')->then(function (array $keys) {
            $this->redisClient->mget(... $keys)->then(function ($values) {
                $this->redisClient->publish(CoasterMonitoringService::REDIS_MONITORING_CHANNEL, json_encode($values));
            });
        });
    }
}