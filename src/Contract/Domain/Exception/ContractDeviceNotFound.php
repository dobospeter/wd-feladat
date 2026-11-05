<?php

declare(strict_types=1);

namespace Inventory\Contract\Domain\Exception;

use Inventory\Contract\Domain\ContractDeviceId;
use Inventory\Contract\Domain\ContractId;
use Inventory\Shared\Domain\Exception\NotFoundException;

final class ContractDeviceNotFound extends NotFoundException
{
    public function __construct(ContractId $contractId, ContractDeviceId $id)
    {
        parent::__construct("Contract {$contractId} has no device assignment {$id}.");
    }

    public function errorCode(): string
    {
        return 'contract_device_not_found';
    }
}
