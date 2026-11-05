<?php

declare(strict_types=1);

namespace App\Modules\Device\Handlers\Payloads;

final readonly class DeviceUpdatePayload extends DevicePayload
{
    public function __construct(
        public string $id,
        string $name,
        string $manufacturer,
        string $model,
        ?string $description,
    ) {
        parent::__construct($name, $manufacturer, $model, $description);
    }
}
