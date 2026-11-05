<?php

declare(strict_types=1);

namespace Inventory\Device\Domain;

use Inventory\Device\Domain\Exception\DeviceNotFound;
use Inventory\Shared\Domain\Pagination\Page;
use Inventory\Shared\Domain\Pagination\PageRequest;

interface DeviceRepository
{
    /**
     * @throws DeviceNotFound
     */
    public function get(DeviceId $id): Device;

    /**
     * @param list<DeviceId> $ids
     * @return array<string, Device> devices keyed by the identifier value
     */
    public function findByIds(array $ids): array;

    /**
     * @return Page<Device>
     */
    public function paginate(PageRequest $request): Page;

    public function save(Device $device): void;

    public function delete(DeviceId $id): void;

    public function existsByManufacturerAndModel(string $manufacturer, string $model, ?DeviceId $except = null): bool;
}
