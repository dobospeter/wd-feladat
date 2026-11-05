<?php

declare(strict_types=1);

namespace App\Modules\Contract\Exceptions;

use Override;
use App\Core\Exceptions\RuleViolationException;

final class InvalidContractPeriodException extends RuleViolationException
{
    public function __construct()
    {
        parent::__construct('The contract must not end before it starts.');
    }

    #[Override]
    public function errorCode(): string
    {
        return 'invalid_contract_period';
    }

    #[Override]
    public function details(): array
    {
        return ['ends_on' => [$this->getMessage()]];
    }
}
