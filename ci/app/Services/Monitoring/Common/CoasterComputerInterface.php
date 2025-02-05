<?php

declare(strict_types=1);

namespace App\Services\Monitoring\Common;

use App\Dto\CoasterDto;
use App\Dto\WagonDto;

interface CoasterComputerInterface
{
    /**
     * @param WagonDto[] $arrayOfWagonDto
     */
    public function compute(CoasterDto $coaster, array $arrayOfWagonDto): int;
}