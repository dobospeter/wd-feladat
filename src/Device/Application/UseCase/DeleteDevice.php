<?php

declare(strict_types=1);

namespace Inventory\Device\Application\UseCase;

use Inventory\Device\Domain\DeviceId;
use Inventory\Device\Domain\DeviceRepository;
use Inventory\Device\Domain\DeviceUsageChecker;
use Inventory\Device\Domain\Exception\DeviceInUse;

final readonly class DeleteDevice
{
    public function __construct(
        private DeviceRepository $devices,
        private DeviceUsageChecker $usage,
    ) {
    }

    public function handle(DeviceId $id): void
    {
        $this->devices->get($id);

        if ($this->usage->isInUse($id)) {
            throw new DeviceInUse($id);
        }

        $this->devices->delete($id);
    }
}
