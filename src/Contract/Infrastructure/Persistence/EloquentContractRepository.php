<?php

declare(strict_types=1);

namespace Inventory\Contract\Infrastructure\Persistence;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Inventory\Contract\Domain\Contract;
use Inventory\Contract\Domain\ContractDevice;
use Inventory\Contract\Domain\ContractDeviceId;
use Inventory\Contract\Domain\ContractId;
use Inventory\Contract\Domain\ContractPeriod;
use Inventory\Contract\Domain\ContractRepository;
use Inventory\Contract\Domain\Exception\ContractNotFound;
use Inventory\Contract\Domain\SerialNumber;
use Inventory\Device\Domain\DeviceId;
use Inventory\Shared\Domain\Pagination\Page;
use Inventory\Shared\Domain\Pagination\PageRequest;

final class EloquentContractRepository implements ContractRepository
{
    private const DATE_FORMAT = 'Y-m-d';

    public function get(ContractId $id): Contract
    {
        $model = ContractModel::query()->with('devices')->find($id->value);

        if ($model === null) {
            throw new ContractNotFound($id);
        }

        return $this->toEntity($model);
    }

    public function paginate(PageRequest $request): Page
    {
        $paginator = ContractModel::query()
            ->with('devices')
            ->orderBy('id')
            ->paginate(perPage: $request->perPage, page: $request->page);

        return new Page(
            $paginator->getCollection()->map(fn (ContractModel $model): Contract => $this->toEntity($model))->all(),
            $paginator->total(),
            $request,
        );
    }

    public function save(Contract $contract): void
    {
        DB::transaction(function () use ($contract): void {
            ContractModel::query()->updateOrCreate(
                ['id' => $contract->id()->value],
                [
                    'contract_number' => $contract->contractNumber(),
                    'partner_name' => $contract->partnerName(),
                    'starts_on' => $contract->period()->startsOn->format(self::DATE_FORMAT),
                    'ends_on' => $contract->period()->endsOn?->format(self::DATE_FORMAT),
                    'notes' => $contract->notes(),
                ],
            );

            $this->syncDevices($contract);
        });
    }

    public function delete(ContractId $id): void
    {
        // The foreign key cascades to the devices placed on the contract.
        ContractModel::query()->whereKey($id->value)->delete();
    }

    public function numberExists(string $contractNumber, ?ContractId $except = null): bool
    {
        return ContractModel::query()
            ->where('contract_number', $contractNumber)
            ->when($except !== null, static fn ($query) => $query->whereKeyNot($except->value))
            ->exists();
    }

    public function unitExists(DeviceId $deviceId, SerialNumber $serialNumber): bool
    {
        return ContractDeviceModel::query()
            ->where('device_id', $deviceId->value)
            ->where('serial_number', $serialNumber->value)
            ->exists();
    }

    /** Units are immutable: sync by deleting the detached ones and inserting the new ones. */
    private function syncDevices(Contract $contract): void
    {
        $contractId = $contract->id()->value;
        $current = array_map(static fn (ContractDevice $unit): string => $unit->id->value, $contract->devices());

        ContractDeviceModel::query()
            ->where('contract_id', $contractId)
            ->whereNotIn('id', $current)
            ->delete();

        $persisted = ContractDeviceModel::query()
            ->where('contract_id', $contractId)
            ->pluck('id')
            ->all();

        foreach ($contract->devices() as $unit) {
            if (in_array($unit->id->value, $persisted, true)) {
                continue;
            }

            ContractDeviceModel::query()->create([
                'id' => $unit->id->value,
                'contract_id' => $contractId,
                'device_id' => $unit->deviceId->value,
                'serial_number' => $unit->serialNumber->value,
            ]);
        }
    }

    private function toEntity(ContractModel $model): Contract
    {
        $devices = $model->devices
            ->map(static fn (ContractDeviceModel $row): ContractDevice => new ContractDevice(
                ContractDeviceId::fromString($row->id),
                DeviceId::fromString($row->device_id),
                SerialNumber::fromString($row->serial_number),
            ))
            ->all();

        return Contract::reconstitute(
            ContractId::fromString($model->id),
            $model->contract_number,
            $model->partner_name,
            new ContractPeriod(
                new DateTimeImmutable($model->starts_on),
                $model->ends_on === null ? null : new DateTimeImmutable($model->ends_on),
            ),
            $model->notes,
            $devices,
        );
    }
}
