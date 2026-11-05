<?php

declare(strict_types=1);

namespace Inventory\Contract\Infrastructure\Persistence;

use Inventory\Device\Domain\DeviceId;
use Inventory\Device\Domain\DeviceUsageChecker;

final class EloquentDeviceUsageChecker implements DeviceUsageChecker
{
    public function isInUse(DeviceId $id): bool
    {
        return ContractDeviceModel::query()->where('device_id', $id->value)->exists();
    }
}
