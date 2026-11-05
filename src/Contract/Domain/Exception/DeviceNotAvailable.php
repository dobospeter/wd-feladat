<?php

declare(strict_types=1);

namespace Inventory\Contract\Domain\Exception;

use Inventory\Device\Domain\DeviceId;
use Inventory\Shared\Domain\Exception\RuleViolationException;

/** Reported as 422 rather than 404: the addressed resource, the contract, does exist. */
final class DeviceNotAvailable extends RuleViolationException
{
    public function __construct(DeviceId $id)
    {
        parent::__construct("Device {$id} does not exist.");
    }

    public function errorCode(): string
    {
        return 'device_not_found';
    }

    public function details(): array
    {
        return ['device_id' => [$this->getMessage()]];
    }
}
