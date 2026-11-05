<?php

declare(strict_types=1);

namespace Inventory\Device\Presentation\Http\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Inventory\Device\Application\UseCase\CreateDevice;
use Inventory\Device\Application\UseCase\DeleteDevice;
use Inventory\Device\Application\UseCase\GetDevice;
use Inventory\Device\Application\UseCase\ListDevices;
use Inventory\Device\Application\UseCase\UpdateDevice;
use Inventory\Device\Domain\DeviceId;
use Inventory\Device\Presentation\Http\Request\SaveDeviceRequest;
use Inventory\Device\Presentation\Http\Resource\DeviceResource;
use Inventory\Shared\Presentation\Http\Request\PaginationRequest;
use Inventory\Shared\Presentation\Http\Response\PaginatedCollection;

final class DeviceController
{
    public function index(PaginationRequest $request, ListDevices $listDevices): AnonymousResourceCollection
    {
        return PaginatedCollection::from($listDevices->handle($request->toPageRequest()), DeviceResource::class);
    }

    public function store(SaveDeviceRequest $request, CreateDevice $createDevice): JsonResponse
    {
        $device = $createDevice->handle($request->toData());

        return (new DeviceResource($device))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('devices.show', ['device' => $device->id()->value]));
    }

    public function show(string $device, GetDevice $getDevice): DeviceResource
    {
        return new DeviceResource($getDevice->handle(DeviceId::fromString($device)));
    }

    public function update(string $device, SaveDeviceRequest $request, UpdateDevice $updateDevice): DeviceResource
    {
        return new DeviceResource($updateDevice->handle(DeviceId::fromString($device), $request->toData()));
    }

    public function destroy(string $device, DeleteDevice $deleteDevice): Response
    {
        $deleteDevice->handle(DeviceId::fromString($device));

        return response()->noContent();
    }
}
