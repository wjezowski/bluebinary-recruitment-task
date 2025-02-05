<?php

declare(strict_types=1);

namespace App\Services\Monitoring\Common;

use App\Dto\CoasterDto;
use App\Dto\WagonDto;
use App\Services\Monitoring\CoasterMonitoringService;

trait WagonsHelperTrait
{
    /**
     * @param WagonDto[] $arrayOfWagonDto
     * @throws \DivisionByZeroError
     */
    private function calculateNumberOfRuns(CoasterDto $coasterDto, array $arrayOfWagonDto): int
    {
        $secondsOfWork = $coasterDto->toHours->getTimestamp() - $coasterDto->fromHours->getTimestamp();

        $secondsPerRun = $coasterDto->lengthOfTrails / $this->getMinVelocity($arrayOfWagonDto);

        return (int) ($secondsOfWork / ($secondsPerRun + CoasterMonitoringService::WAGON_5_MINUTES_BREAK));
    }

    /**
     * I'm assuming that wagons are "smart" and the velocity of every wagon is the same as velocity of the slowest one.
     * It's because to avoid crashes. I'm assuming wagons cannot jump over other wagons.
     *
     * @param WagonDto[] $arrayOfWagonDto
     */
    private function getMinVelocity(array $arrayOfWagonDto): float
    {
        if (empty($arrayOfWagonDto)) {
            return 0.0;
        }

        $lowestVelocity = null;

        foreach ($arrayOfWagonDto as $wagonDto) {
            if (is_null($lowestVelocity)) {
                $lowestVelocity = $wagonDto->velocity;

                continue;
            }

            if ($wagonDto->velocity < $lowestVelocity) {
                $lowestVelocity = $wagonDto->velocity;
            }
        }

        return $lowestVelocity;
    }
}