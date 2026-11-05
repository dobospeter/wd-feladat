<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers;

use App\Modules\Device\Repositories\DeviceRepository;
use App\Modules\Contract\Repositories\ContractRepository;
use App\Modules\Device\Exceptions\DeviceNotFoundException;
use App\Modules\Contract\Repositories\ContractDeviceRepository;
use App\Modules\Contract\Exceptions\DeviceNotAvailableException;
use App\Modules\Contract\Handlers\Results\ContractDetailsResult;
use App\Modules\Contract\Handlers\Payloads\ContractDeviceAttachPayload;
use App\Modules\Contract\Exceptions\SerialNumberAlreadyAssignedException;

final readonly class ContractDeviceAttachHandler
{
    public function __construct(
        private ContractRepository $contracts,
        private DeviceRepository $devices,
        private ContractDeviceRepository $contractDevices,
    ) {
    }

    public function handle(ContractDeviceAttachPayload $payload): ContractDetailsResult
    {
        $contract = $this->contracts->get($payload->contractId);

        try {
            $this->devices->get($payload->deviceId);
        } catch (DeviceNotFoundException) {
            throw new DeviceNotAvailableException($payload->deviceId);
        }

        if ($this->contractDevices->unitExists($payload->deviceId, $payload->serialNumber)) {
            throw new SerialNumberAlreadyAssignedException($payload->deviceId, $payload->serialNumber);
        }

        $this->contractDevices->create($contract->id, $payload->deviceId, $payload->serialNumber);

        return ContractDetailsResult::fromModel($this->contracts->loadDetails($contract));
    }
}
