<?php

declare(strict_types=1);

namespace App\Services\Monitoring\Computers;

use App\Dto\CoasterDto;
use App\Dto\WagonDto;
use App\Services\Monitoring\Common\CoasterComputerInterface;
use App\Services\Monitoring\Common\WagonsHelperTrait;

final class MaxNumberOfClientsComputer implements CoasterComputerInterface
{
    use WagonsHelperTrait;

    /**
     * @param WagonDto[] $arrayOfWagonDto
     */
    public function compute(CoasterDto $coaster, array $arrayOfWagonDto): int
    {
        $numberOfRuns = $this->calculateNumberOfRuns($coaster, $arrayOfWagonDto);

        $maxNumberOfClients = 0;

        foreach ($arrayOfWagonDto as $wagonDto) {
            $maxNumberOfClients += $wagonDto->numberOfSeats * $numberOfRuns;
        }

        return $maxNumberOfClients;
    }
}