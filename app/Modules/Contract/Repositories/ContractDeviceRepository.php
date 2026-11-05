<?php

declare(strict_types=1);

namespace App\Modules\Contract\Repositories;

use App\Modules\Contract\Exceptions\ContractDeviceNotFoundException;
use App\Modules\Contract\Models\ContractDevice;

final class ContractDeviceRepository
{
    public function create(string $contractId, string $deviceId, string $serialNumber): ContractDevice
    {
        return ContractDevice::query()->create([
            'contract_id' => $contractId,
            'device_id' => $deviceId,
            'serial_number' => $serialNumber,
        ]);
    }

    public function getInContract(string $contractId, string $assignmentId): ContractDevice
    {
        return ContractDevice::query()
            ->where('contract_id', $contractId)
            ->find($assignmentId) ?? throw new ContractDeviceNotFoundException($contractId, $assignmentId);
    }

    public function delete(ContractDevice $contractDevice): void
    {
        $contractDevice->delete();
    }

    public function unitExists(string $deviceId, string $serialNumber): bool
    {
        return ContractDevice::query()
            ->where('device_id', $deviceId)
            ->where('serial_number', $serialNumber)
            ->exists();
    }
}
