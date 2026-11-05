<?php

declare(strict_types=1);

namespace App\Api\Device\Controllers;

use App\Api\Device\Requests\DeviceSaveRequest;
use App\Api\Device\Resources\DeviceResource;
use App\Modules\Device\Handlers\DeviceCreateHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class DeviceCreateController
{
    public function __invoke(DeviceSaveRequest $request, DeviceCreateHandler $handler): JsonResponse
    {
        $device = $handler->handle($request->toCreatePayload());

        return (new DeviceResource($device))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('devices.show', ['device' => $device->id]));
    }
}
