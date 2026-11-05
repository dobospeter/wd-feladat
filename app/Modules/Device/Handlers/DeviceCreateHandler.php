<?php

declare(strict_types=1);

namespace App\Modules\Device\Handlers;

use App\Modules\Device\Exceptions\DeviceAlreadyExistsException;
use App\Modules\Device\Handlers\Payloads\DeviceCreatePayload;
use App\Modules\Device\Handlers\Results\DeviceResult;
use App\Modules\Device\Repositories\DeviceRepository;

final readonly class DeviceCreateHandler
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    public function handle(DeviceCreatePayload $payload): DeviceResult
    {
        if ($this->devices->existsByManufacturerAndModel($payload->manufacturer, $payload->model)) {
            throw new DeviceAlreadyExistsException($payload->manufacturer, $payload->model);
        }

        return DeviceResult::fromModel($this->devices->create($payload->attributes()));
    }
}
