<?php

declare(strict_types=1);

namespace App\Modules\Device\Exceptions;

use Override;
use App\Core\Exceptions\ConflictException;

final class DeviceAlreadyExistsException extends ConflictException
{
    public function __construct(string $manufacturer, string $model)
    {
        parent::__construct("A device with manufacturer '{$manufacturer}' and model '{$model}' already exists.");
    }

    #[Override]
    public function errorCode(): string
    {
        return 'device_already_exists';
    }
}
