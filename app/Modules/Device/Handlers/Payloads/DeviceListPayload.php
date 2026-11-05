<?php

declare(strict_types=1);

namespace App\Modules\Device\Handlers\Payloads;

use App\Core\Pagination\PageRequest;

final readonly class DeviceListPayload
{
    public function __construct(public PageRequest $pagination)
    {
    }
}
