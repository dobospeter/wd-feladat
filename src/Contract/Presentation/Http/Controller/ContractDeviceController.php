<?php

declare(strict_types=1);

namespace Inventory\Contract\Presentation\Http\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Inventory\Contract\Application\UseCase\AttachDevice;
use Inventory\Contract\Application\UseCase\DetachDevice;
use Inventory\Contract\Domain\ContractDeviceId;
use Inventory\Contract\Domain\ContractId;
use Inventory\Contract\Presentation\Http\Request\AttachDeviceRequest;
use Inventory\Contract\Presentation\Http\Resource\ContractDetailsResource;
use Inventory\Device\Domain\DeviceId;

final class ContractDeviceController
{
    public function store(string $contract, AttachDeviceRequest $request, AttachDevice $attachDevice): JsonResponse
    {
        $details = $attachDevice->handle(
            ContractId::fromString($contract),
            DeviceId::fromString($request->deviceId()),
            $request->serialNumber(),
        );

        return (new ContractDetailsResource($details))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('contracts.show', ['contract' => $contract]));
    }

    public function destroy(string $contract, string $assignment, DetachDevice $detachDevice): Response
    {
        $detachDevice->handle(ContractId::fromString($contract), ContractDeviceId::fromString($assignment));

        return response()->noContent();
    }
}
