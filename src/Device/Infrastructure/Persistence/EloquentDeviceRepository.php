<?php

declare(strict_types=1);

namespace Inventory\Device\Infrastructure\Persistence;

use Inventory\Device\Domain\Device;
use Inventory\Device\Domain\DeviceId;
use Inventory\Device\Domain\DeviceRepository;
use Inventory\Device\Domain\Exception\DeviceNotFound;
use Inventory\Shared\Domain\Pagination\Page;
use Inventory\Shared\Domain\Pagination\PageRequest;

final class EloquentDeviceRepository implements DeviceRepository
{
    public function get(DeviceId $id): Device
    {
        $model = DeviceModel::query()->find($id->value);

        if ($model === null) {
            throw new DeviceNotFound($id);
        }

        return $this->toEntity($model);
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $devices = [];
        $models = DeviceModel::query()
            ->whereIn('id', array_map(static fn (DeviceId $id): string => $id->value, $ids))
            ->get();

        foreach ($models as $model) {
            $devices[$model->id] = $this->toEntity($model);
        }

        return $devices;
    }

    public function paginate(PageRequest $request): Page
    {
        $paginator = DeviceModel::query()
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(perPage: $request->perPage, page: $request->page);

        return new Page(
            $paginator->getCollection()->map(fn (DeviceModel $model): Device => $this->toEntity($model))->all(),
            $paginator->total(),
            $request,
        );
    }

    public function save(Device $device): void
    {
        DeviceModel::query()->updateOrCreate(
            ['id' => $device->id()->value],
            [
                'name' => $device->name(),
                'manufacturer' => $device->manufacturer(),
                'model' => $device->model(),
                'description' => $device->description(),
            ],
        );
    }

    public function delete(DeviceId $id): void
    {
        DeviceModel::query()->whereKey($id->value)->delete();
    }

    public function existsByManufacturerAndModel(string $manufacturer, string $model, ?DeviceId $except = null): bool
    {
        return DeviceModel::query()
            ->where('manufacturer', $manufacturer)
            ->where('model', $model)
            ->when($except !== null, static fn ($query) => $query->whereKeyNot($except->value))
            ->exists();
    }

    private function toEntity(DeviceModel $model): Device
    {
        return Device::reconstitute(
            DeviceId::fromString($model->id),
            $model->name,
            $model->manufacturer,
            $model->model,
            $model->description,
        );
    }
}
