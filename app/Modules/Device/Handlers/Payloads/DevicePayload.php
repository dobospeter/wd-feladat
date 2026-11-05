<?php

declare(strict_types=1);

namespace App\Modules\Device\Handlers\Payloads;

abstract readonly class DevicePayload
{
    public function __construct(
        public string $name,
        public string $manufacturer,
        public string $model,
        public ?string $description,
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function attributes(): array
    {
        return [
            'name' => $this->name,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'description' => $this->description,
        ];
    }
}
