<?php

declare(strict_types=1);

namespace App\Modules\Device\Handlers;

use App\Modules\Device\Handlers\Results\DeviceResult;
use App\Modules\Device\Repositories\DeviceRepository;
use App\Modules\Device\Handlers\Payloads\DeviceUpdatePayload;
use App\Modules\Device\Exceptions\DeviceAlreadyExistsException;

final readonly class DeviceUpdateHandler
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    public function handle(DeviceUpdatePayload $payload): DeviceResult
    {
        $device = $this->devices->get($payload->id);

        if ($this->devices->existsByManufacturerAndModel($payload->manufacturer, $payload->model, $payload->id)) {
            throw new DeviceAlreadyExistsException($payload->manufacturer, $payload->model);
        }

        return DeviceResult::fromModel($this->devices->update($device, $payload->attributes()));
    }
}
