<?php

declare(strict_types=1);

namespace App\Modules\Party\Exceptions;

use Override;
use App\Core\Exceptions\ConflictException;

final class PartyAlreadyExistsException extends ConflictException
{
    public function __construct(string $taxNumber)
    {
        parent::__construct("A party with tax number '{$taxNumber}' already exists.");
    }

    #[Override]
    public function errorCode(): string
    {
        return 'party_already_exists';
    }
}
