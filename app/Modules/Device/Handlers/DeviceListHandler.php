<?php

declare(strict_types=1);

namespace App\Modules\Device\Handlers;

use App\Core\Pagination\Page;
use App\Modules\Device\Handlers\Payloads\DeviceListPayload;
use App\Modules\Device\Handlers\Results\DeviceResult;
use App\Modules\Device\Models\Device;
use App\Modules\Device\Repositories\DeviceRepository;

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
        $paginator = $this->devices->paginate($payload->pagination);

        return new Page(
            array_map(static fn (Device $device): DeviceResult => DeviceResult::fromModel($device), $paginator->items()),
            $paginator->total(),
            $payload->pagination,
        );
    }
}
