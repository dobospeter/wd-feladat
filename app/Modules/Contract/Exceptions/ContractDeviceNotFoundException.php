<?php

declare(strict_types=1);

namespace App\Modules\Contract\Exceptions;

use Override;
use App\Core\Exceptions\NotFoundException;

final class ContractDeviceNotFoundException extends NotFoundException
{
    public function __construct(string $contractId, string $assignmentId)
    {
        parent::__construct("Contract {$contractId} has no device assignment {$assignmentId}.");
    }

    #[Override]
    public function errorCode(): string
    {
        return 'contract_device_not_found';
    }
}
