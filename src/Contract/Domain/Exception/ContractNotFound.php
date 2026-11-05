<?php

declare(strict_types=1);

namespace Inventory\Contract\Domain\Exception;

use Inventory\Contract\Domain\ContractId;
use Inventory\Shared\Domain\Exception\NotFoundException;

final class ContractNotFound extends NotFoundException
{
    public function __construct(ContractId $id)
    {
        parent::__construct("Contract {$id} was not found.");
    }

    public function errorCode(): string
    {
        return 'contract_not_found';
    }
}
