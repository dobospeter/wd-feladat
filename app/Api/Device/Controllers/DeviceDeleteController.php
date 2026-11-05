<?php

declare(strict_types=1);

namespace App\Api\Device\Controllers;

use App\Modules\Device\Handlers\DeviceDeleteHandler;
use App\Modules\Device\Handlers\Payloads\DeviceDeletePayload;
use Illuminate\Http\Response;

final class DeviceDeleteController
{
    public function __invoke(string $device, DeviceDeleteHandler $handler): Response
    {
        $handler->handle(new DeviceDeletePayload($device));

        return response()->noContent();
    }
}
