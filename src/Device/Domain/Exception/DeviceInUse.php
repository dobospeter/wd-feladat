<?php

declare(strict_types=1);

namespace Inventory\Device\Domain\Exception;

use Inventory\Device\Domain\DeviceId;
use Inventory\Shared\Domain\Exception\ConflictException;

final class DeviceInUse extends ConflictException
{
    public function __construct(DeviceId $id)
    {
        parent::__construct("Device {$id} is placed on at least one contract and cannot be deleted.");
    }

    public function errorCode(): string
    {
        return 'device_in_use';
    }
}
