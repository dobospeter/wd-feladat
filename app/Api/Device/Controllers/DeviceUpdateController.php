<?php

declare(strict_types=1);

namespace App\Api\Device\Controllers;

use App\Api\Device\Requests\DeviceSaveRequest;
use App\Api\Device\Resources\DeviceResource;
use App\Modules\Device\Handlers\DeviceUpdateHandler;

final class DeviceUpdateController
{
    public function __invoke(string $device, DeviceSaveRequest $request, DeviceUpdateHandler $handler): DeviceResource
    {
        return new DeviceResource($handler->handle($request->toUpdatePayload($device)));
    }
}
