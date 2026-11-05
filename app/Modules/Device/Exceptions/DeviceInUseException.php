<?php

declare(strict_types=1);

namespace App\Modules\Device\Exceptions;

use Override;
use App\Core\Exceptions\ConflictException;

final class DeviceInUseException extends ConflictException
{
    public function __construct(string $id)
    {
        parent::__construct("Device {$id} is placed on at least one contract and cannot be deleted.");
    }

    #[Override]
    public function errorCode(): string
    {
        return 'device_in_use';
    }
}
