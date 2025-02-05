<?php

declare(strict_types=1);

namespace App\Commands;

use App\Dto\CoasterDto;
use App\Dto\WagonDto;
use App\Libraries\ClueRedisClient\RedisClientFactory;
use App\Services\Monitoring\CoasterMonitoringService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Commands;
use Psr\Log\LoggerInterface;

final class CoastersMonitoring extends BaseCommand
{
    protected $group       = 'Coasters';
    protected $name        = 'app:coasters-monitoring';
    protected $description = 'Monitoring status of coasters.';

    private CoasterMonitoringService $coasterMonitoringService;

    public function __construct(LoggerInterface $logger, Commands $commands)
    {
        parent::__construct($logger, $commands);

        $this->coasterMonitoringService = new CoasterMonitoringService();
    }

    public function run(array $params): void
    {
        CLI::write('Monitoring status');

        $redis = RedisClientFactory::getInstance();

        $redis->subscribe(CoasterMonitoringService::REDIS_MONITORING_CHANNEL);

        $redis->on('message', function (string $channel, string $payload) {
            $payload = json_decode($payload, true);

            $coasters = [];
            $wagons = [];

            foreach ($payload as $row) {
                /** @var CoasterDto|WagonDto $dto */
                $dto = unserialize($row);

                if (is_a($dto, CoasterDto::class)) {
                    $coasters[] = $dto;
                } else {
                    $wagons[$dto->coasterId][] = $dto;
                }
            }

            $this->handleData($coasters, $wagons);
        });
    }

    /**
     * @param CoasterDto[] $coasters
     * @param WagonDto[][] $wagons
     */
    private function handleData(array $coasters, array $wagons): void
    {
        CLI::clearScreen();

        foreach ($coasters as $coasterDto) {
            $this->renderMonitoring($this->coasterMonitoringService->monitor($coasterDto, $wagons[$coasterDto->coasterId] ?? []));
        }
    }

    private function renderMonitoring(array $lines): void
    {
        foreach ($lines as $line) {
            CLI::write($line);
        }

        CLI::newLine();
    }
}