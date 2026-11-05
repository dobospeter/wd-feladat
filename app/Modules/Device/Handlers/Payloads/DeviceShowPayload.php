<?php

declare(strict_types=1);

namespace App\Modules\Device\Handlers\Payloads;

final readonly class DeviceShowPayload
{
    public function __construct(public string $id)
    {
    }
}
