<?php

declare(strict_types=1);

namespace App\Modules\Contract\Exceptions;

use Override;
use App\Core\Exceptions\ConflictException;

final class ContractNumberAlreadyExistsException extends ConflictException
{
    public function __construct(string $contractNumber)
    {
        parent::__construct("A contract with number '{$contractNumber}' already exists.");
    }

    #[Override]
    public function errorCode(): string
    {
        return 'contract_number_already_exists';
    }
}
