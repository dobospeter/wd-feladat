<?php

declare(strict_types=1);

namespace Inventory\Contract\Domain\Exception;

use Inventory\Contract\Domain\SerialNumber;
use Inventory\Device\Domain\DeviceId;
use Inventory\Shared\Domain\Exception\ConflictException;

final class SerialNumberAlreadyAssigned extends ConflictException
{
    public function __construct(DeviceId $deviceId, SerialNumber $serialNumber)
    {
        parent::__construct("The unit of device {$deviceId} with serial number '{$serialNumber}' is already placed on a contract.");
    }

    public function errorCode(): string
    {
        return 'serial_number_already_assigned';
    }
}
