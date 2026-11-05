<?php

declare(strict_types=1);

namespace Inventory\Device\Domain\Exception;

use Inventory\Shared\Domain\Exception\ConflictException;

final class DeviceAlreadyExists extends ConflictException
{
    public function __construct(string $manufacturer, string $model)
    {
        parent::__construct("A device with manufacturer '{$manufacturer}' and model '{$model}' already exists.");
    }

    public function errorCode(): string
    {
        return 'device_already_exists';
    }
}
