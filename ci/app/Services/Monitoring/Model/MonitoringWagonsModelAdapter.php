<?php

declare(strict_types=1);

namespace App\Services\Monitoring\Model;

use App\Dto\WagonDto;
use App\Models\Wagons\WagonsModel;
use App\Services\Monitoring\Common\PublishUpdateTrait;

final readonly class MonitoringWagonsModelAdapter extends WagonsModel
{
    use PublishUpdateTrait;

    public function saveWagon(WagonDto $wagonDto): void
    {
        parent::saveWagon($wagonDto);

        $this->publishUpdate();
    }

    public function deleteWagon(string $coasterId, string $wagonId): void
    {
        parent::deleteWagon($coasterId, $wagonId);

        $this->publishUpdate();
    }
}