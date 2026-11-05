<?php

declare(strict_types=1);

namespace Inventory\Device\Domain\Exception;

use Inventory\Device\Domain\DeviceId;
use Inventory\Shared\Domain\Exception\NotFoundException;

final class DeviceNotFound extends NotFoundException
{
    public function __construct(DeviceId $id)
    {
        parent::__construct("Device {$id} was not found.");
    }

    public function errorCode(): string
    {
        return 'device_not_found';
    }
}
