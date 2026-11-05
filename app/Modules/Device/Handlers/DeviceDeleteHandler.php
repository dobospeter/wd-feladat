<?php

declare(strict_types=1);

namespace App\Modules\Device\Handlers;

use App\Modules\Device\Handlers\Payloads\DeviceDeletePayload;
use App\Modules\Device\Repositories\DeviceRepository;

final readonly class DeviceDeleteHandler
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    public function handle(DeviceDeletePayload $payload): void
    {
        $this->devices->delete($this->devices->get($payload->id));
    }
}
