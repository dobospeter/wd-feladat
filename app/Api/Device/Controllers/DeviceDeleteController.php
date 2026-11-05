<?php

declare(strict_types=1);

namespace App\Api\Device\Controllers;

use Illuminate\Http\Response;
use App\Modules\Device\Handlers\DeviceDeleteHandler;
use App\Modules\Device\Handlers\Payloads\DeviceDeletePayload;

final class DeviceDeleteController
{
    public function __invoke(string $device, DeviceDeleteHandler $handler): Response
    {
        $handler->handle(new DeviceDeletePayload($device));

        return response()->noContent();
    }
}
