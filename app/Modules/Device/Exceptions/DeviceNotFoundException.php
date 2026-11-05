<?php

declare(strict_types=1);

namespace App\Modules\Device\Exceptions;

use Override;
use App\Core\Exceptions\NotFoundException;

final class DeviceNotFoundException extends NotFoundException
{
    public function __construct(string $id)
    {
        parent::__construct("Device {$id} was not found.");
    }

    #[Override]
    public function errorCode(): string
    {
        return 'device_not_found';
    }
}
