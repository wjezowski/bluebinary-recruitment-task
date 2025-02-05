<?php

declare(strict_types=1);

use App\Dto\CoasterDto;
use App\Dto\WagonDto;
use App\Libraries\ClueRedisClient\RedisClientFactory;
use App\Services\Monitoring\CoasterMonitoringService;

require_once 'vendor/autoload.php';

$coasterMonitoringService = new CoasterMonitoringService();

function cls(): void
{
    echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
    echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
}

function renderMonitoring(array $lines): void
{
    foreach ($lines as $line) {
        echo $line;
    }
}

/**
 * @param CoasterDto[] $coasters
 * @param WagonDto[][] $wagons
 * @param CoasterMonitoringService $coasterMonitoringService
 * @return void
 */
function handleData(array $coasters, array $wagons, CoasterMonitoringService $coasterMonitoringService): void
{
    cls();

    foreach ($coasters as $coasterDto) {
        renderMonitoring($coasterMonitoringService->monitor($coasterDto, $wagons[$coasterDto->coasterId]));
    }
}

$redis = RedisClientFactory::getInstance();

$redis->subscribe(CoasterMonitoringService::REDIS_MONITORING_CHANNEL);

$redis->on('message', function (string $channel, string $payload) use ($coasterMonitoringService) {
    $payload = json_decode($payload, true);

    $coasters = [];
    $wagons = [];

    foreach ($payload as &$row) {
        /** @var CoasterDto|WagonDto $row */
        $row = unserialize($row);

        if (is_a($row, CoasterDto::class)) {
            $coasters[] = $row;
        } else {
            $wagons[$row->coasterId][] = $row;
        }
    }

    handleData($coasters, $wagons, $coasterMonitoringService);

});