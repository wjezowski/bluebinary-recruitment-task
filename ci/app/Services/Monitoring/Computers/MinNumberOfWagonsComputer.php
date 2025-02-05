<?php

declare(strict_types=1);

namespace App\Services\Monitoring\Computers;

use App\Dto\CoasterDto;
use App\Dto\WagonDto;
use App\Services\Monitoring\Common\CoasterComputerInterface;
use App\Services\Monitoring\Common\WagonsHelperTrait;

final class MinNumberOfWagonsComputer implements CoasterComputerInterface
{
    use WagonsHelperTrait;

    public const int DEFAULT_WAGON_NUMBER_OF_PLACES = 32;

    private MaxNumberOfClientsComputer $maxNumberOfClientsComputer;

    public function __construct()
    {
        $this->maxNumberOfClientsComputer = new MaxNumberOfClientsComputer();
    }

    /**
     * @param WagonDto[] $arrayOfWagonDto
     */
    public function compute(CoasterDto $coaster, array $arrayOfWagonDto): int
    {
        $currentMaxNumberOfClients = $this->maxNumberOfClientsComputer->compute($coaster, $arrayOfWagonDto);

        $differenceOfNumberOfClients = $currentMaxNumberOfClients - $coaster->numberOfClients;

        $differenceOfClientsPerRun = $differenceOfNumberOfClients / $this->calculateNumberOfRuns($coaster, $arrayOfWagonDto);

        $differenceInNumberOfWagons = $differenceOfClientsPerRun / self::DEFAULT_WAGON_NUMBER_OF_PLACES;

        if (intval($differenceInNumberOfWagons) > $differenceInNumberOfWagons) {
            $differenceInNumberOfWagons = intval($differenceInNumberOfWagons) - 1;
        }

        return sizeof($arrayOfWagonDto) - intval($differenceInNumberOfWagons);
    }
}