<?php

declare(strict_types=1);

namespace App\Modules\Device\Repositories;

use App\Core\Pagination\PageRequest;
use App\Modules\Device\Models\Device;
use Illuminate\Database\QueryException;
use App\Modules\Device\Exceptions\DeviceInUseException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Modules\Device\Exceptions\DeviceNotFoundException;

final class DeviceRepository
{
    public function get(string $id): Device
    {
        return Device::query()->find($id) ?? throw new DeviceNotFoundException($id);
    }

    /**
     * @return LengthAwarePaginator<int, Device>
     */
    public function paginate(PageRequest $request): LengthAwarePaginator
    {
        return Device::query()
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(perPage: $request->perPage, page: $request->page);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Device
    {
        return Device::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Device $device, array $attributes): Device
    {
        $device->update($attributes);

        return $device;
    }

    public function delete(Device $device): void
    {
        try {
            Device::query()->whereKey($device->getKey())->delete();
        } catch (QueryException $e) {
            // The only constraint that can reject a delete is the contract_devices foreign key.
            if ((string) $e->getCode() === '23000') {
                throw new DeviceInUseException($device->id);
            }

            throw $e;
        }
    }

    public function existsByManufacturerAndModel(string $manufacturer, string $model, ?string $exceptId = null): bool
    {
        return Device::query()
            ->where('manufacturer', $manufacturer)
            ->where('model', $model)
            ->when($exceptId !== null, static fn ($query) => $query->whereKeyNot($exceptId))
            ->exists();
    }
}
