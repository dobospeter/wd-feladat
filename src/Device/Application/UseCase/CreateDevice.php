<?php

declare(strict_types=1);

namespace Inventory\Device\Application\UseCase;

use Inventory\Device\Application\Data\DeviceData;
use Inventory\Device\Domain\Device;
use Inventory\Device\Domain\DeviceId;
use Inventory\Device\Domain\DeviceRepository;
use Inventory\Device\Domain\Exception\DeviceAlreadyExists;

final readonly class CreateDevice
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    public function handle(DeviceData $data): Device
    {
        $device = Device::register(
            DeviceId::generate(),
            $data->name,
            $data->manufacturer,
            $data->model,
            $data->description,
        );

        if ($this->devices->existsByManufacturerAndModel($device->manufacturer(), $device->model())) {
            throw new DeviceAlreadyExists($device->manufacturer(), $device->model());
        }

        $this->devices->save($device);

        return $device;
    }
}
