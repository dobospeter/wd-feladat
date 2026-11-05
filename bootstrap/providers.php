<?php

use Inventory\ApiLog\Infrastructure\ApiLogServiceProvider;
use Inventory\Contract\Infrastructure\ContractServiceProvider;
use Inventory\Device\Infrastructure\DeviceServiceProvider;
use Inventory\Docs\Infrastructure\DocsServiceProvider;

return [
    DeviceServiceProvider::class,
    ContractServiceProvider::class,
    ApiLogServiceProvider::class,
    DocsServiceProvider::class,
];
