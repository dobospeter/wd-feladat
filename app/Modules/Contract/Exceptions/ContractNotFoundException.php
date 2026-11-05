<?php

declare(strict_types=1);

namespace App\Modules\Contract\Exceptions;

use App\Core\Exceptions\NotFoundException;

final class ContractNotFoundException extends NotFoundException
{
    public function __construct(string $id)
    {
        parent::__construct("Contract {$id} was not found.");
    }

    public function errorCode(): string
    {
        return 'contract_not_found';
    }
}
