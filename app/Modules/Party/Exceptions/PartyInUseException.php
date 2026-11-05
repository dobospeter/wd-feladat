<?php

declare(strict_types=1);

namespace App\Modules\Party\Exceptions;

use Override;
use App\Core\Exceptions\ConflictException;

final class PartyInUseException extends ConflictException
{
    public function __construct(string $id)
    {
        parent::__construct("Party {$id} has contracts and cannot be deleted.");
    }

    #[Override]
    public function errorCode(): string
    {
        return 'party_in_use';
    }
}
