<?php

declare(strict_types=1);

namespace App\Services\Monitoring\Model;

use App\Dto\CoasterDto;
use App\Models\Coasters\CoastersModel;
use App\Services\Monitoring\Common\PublishUpdateTrait;

final readonly class MonitoringCoastersModelAdapter extends CoastersModel
{
    use PublishUpdateTrait;

    public function saveCoaster(CoasterDto $coasterDto): void
    {
        parent::saveCoaster($coasterDto);

        $this->publishUpdate();
    }
}