<?php

declare(strict_types=1);

namespace App\Modules\Device\Handlers;

use App\Core\Pagination\Page;
use App\Modules\Device\Handlers\Results\DeviceResult;
use App\Modules\Device\Repositories\DeviceRepository;
use App\Modules\Device\Handlers\Payloads\DeviceListPayload;

final readonly class DeviceListHandler
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    /**
     * @return Page<DeviceResult>
     */
    public function handle(DeviceListPayload $payload): Page
    {
        return Page::fromPaginator(
            $this->devices->paginate($payload->pagination),
            $payload->pagination,
            DeviceResult::fromModel(...),
        );
    }
}
