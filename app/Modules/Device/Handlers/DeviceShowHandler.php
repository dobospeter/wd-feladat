<?php

declare(strict_types=1);

namespace App\Modules\Device\Handlers;

use App\Modules\Device\Handlers\Results\DeviceResult;
use App\Modules\Device\Repositories\DeviceRepository;
use App\Modules\Device\Handlers\Payloads\DeviceShowPayload;

final readonly class DeviceShowHandler
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    public function handle(DeviceShowPayload $payload): DeviceResult
    {
        return DeviceResult::fromModel($this->devices->get($payload->id));
    }
}
