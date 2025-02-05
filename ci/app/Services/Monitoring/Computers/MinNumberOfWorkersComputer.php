<?php

declare(strict_types=1);

namespace App\Services\Monitoring\Computers;

use App\Dto\CoasterDto;
use App\Dto\WagonDto;
use App\Services\Monitoring\Common\CoasterComputerInterface;

final class MinNumberOfWorkersComputer implements CoasterComputerInterface
{
    public const int WORKERS_PER_COASTER = 1;
    public const int WORKERS_PER_WAGON = 2;

    /**
     * @param WagonDto[] $arrayOfWagonDto
     */
    public function compute(CoasterDto $coaster, array $arrayOfWagonDto): int
    {
        return $this->computeNumberOfWorkersForNumberOfWagons(count($arrayOfWagonDto)) + self::WORKERS_PER_COASTER;
    }

    public function computeNumberOfWorkersForNumberOfWagons(int $numberOfWagons): int
    {
        return $numberOfWagons * self::WORKERS_PER_WAGON;
    }
}