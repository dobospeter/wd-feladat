<?php

declare(strict_types=1);

namespace App\Modules\Contract\Exceptions;

use Override;
use App\Core\Exceptions\RuleViolationException;

/** Reported as 422 rather than 404: the addressed resource, the contract, does exist. */
final class DeviceNotAvailableException extends RuleViolationException
{
    public function __construct(string $id)
    {
        parent::__construct("Device {$id} does not exist.");
    }

    #[Override]
    public function errorCode(): string
    {
        return 'device_not_found';
    }

    #[Override]
    public function details(): array
    {
        return ['device_id' => [$this->getMessage()]];
    }
}
