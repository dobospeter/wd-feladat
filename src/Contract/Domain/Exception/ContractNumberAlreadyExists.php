<?php

declare(strict_types=1);

namespace Inventory\Contract\Domain\Exception;

use Inventory\Shared\Domain\Exception\ConflictException;

final class ContractNumberAlreadyExists extends ConflictException
{
    public function __construct(string $contractNumber)
    {
        parent::__construct("A contract with number '{$contractNumber}' already exists.");
    }

    public function errorCode(): string
    {
        return 'contract_number_already_exists';
    }
}
