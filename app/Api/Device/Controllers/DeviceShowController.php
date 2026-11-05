<?php

declare(strict_types=1);

namespace App\Api\Device\Controllers;

use App\Api\Device\Resources\DeviceResource;
use App\Modules\Device\Handlers\DeviceShowHandler;
use App\Modules\Device\Handlers\Payloads\DeviceShowPayload;

final class DeviceShowController
{
    public function __invoke(string $device, DeviceShowHandler $handler): DeviceResource
    {
        return new DeviceResource($handler->handle(new DeviceShowPayload($device)));
    }
}
