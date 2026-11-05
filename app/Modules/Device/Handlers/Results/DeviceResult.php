<?php

declare(strict_types=1);

namespace App\Modules\Device\Handlers\Results;

use App\Modules\Device\Models\Device;

final readonly class DeviceResult
{
    public function __construct(
        public string $id,
        public string $name,
        public string $manufacturer,
        public string $model,
        public ?string $description,
    ) {
    }

    public static function fromModel(Device $device): self
    {
        return new self($device->id, $device->name, $device->manufacturer, $device->model, $device->description);
    }
}
