<?php

declare(strict_types=1);

namespace Inventory\Device\Domain;

/** Implemented by the Contract context, so the Device context does not depend on it. */
interface DeviceUsageChecker
{
    public function isInUse(DeviceId $id): bool;
}
