<?php

declare(strict_types=1);

namespace Inventory\ApiLog\Domain;

interface ApiLogRepository
{
    public function save(ApiLogEntry $entry): void;
}
