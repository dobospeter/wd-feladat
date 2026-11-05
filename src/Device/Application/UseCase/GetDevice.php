<?php

declare(strict_types=1);

namespace Inventory\Device\Application\UseCase;

use Inventory\Device\Domain\Device;
use Inventory\Device\Domain\DeviceId;
use Inventory\Device\Domain\DeviceRepository;

final readonly class GetDevice
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    public function handle(DeviceId $id): Device
    {
        return $this->devices->get($id);
    }
}
