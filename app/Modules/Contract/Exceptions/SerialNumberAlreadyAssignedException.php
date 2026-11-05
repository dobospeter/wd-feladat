<?php

declare(strict_types=1);

namespace App\Modules\Contract\Exceptions;

use Override;
use App\Core\Exceptions\ConflictException;

final class SerialNumberAlreadyAssignedException extends ConflictException
{
    public function __construct(string $deviceId, string $serialNumber)
    {
        parent::__construct(
            "The unit of device {$deviceId} with serial number '{$serialNumber}' is already placed on a contract.",
        );
    }

    #[Override]
    public function errorCode(): string
    {
        return 'serial_number_already_assigned';
    }
}
