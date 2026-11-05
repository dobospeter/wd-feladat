<?php

declare(strict_types=1);

namespace App\Modules\Device\Handlers\Payloads;

final readonly class DeviceDeletePayload
{
    public function __construct(public string $id)
    {
    }
}
