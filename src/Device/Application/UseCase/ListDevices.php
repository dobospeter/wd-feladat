<?php

declare(strict_types=1);

namespace Inventory\Device\Application\UseCase;

use Inventory\Device\Domain\Device;
use Inventory\Device\Domain\DeviceRepository;
use Inventory\Shared\Domain\Pagination\Page;
use Inventory\Shared\Domain\Pagination\PageRequest;

final readonly class ListDevices
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    /**
     * @return Page<Device>
     */
    public function handle(PageRequest $request): Page
    {
        return $this->devices->paginate($request);
    }
}
