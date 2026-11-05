<?php

declare(strict_types=1);

namespace Inventory\Device\Application\Data;

final readonly class DeviceData
{
    public function __construct(
        public string $name,
        public string $manufacturer,
        public string $model,
        public ?string $description,
    ) {
    }
}
