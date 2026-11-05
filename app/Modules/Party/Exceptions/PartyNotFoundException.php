<?php

declare(strict_types=1);

namespace App\Modules\Party\Exceptions;

use App\Core\Exceptions\NotFoundException;

final class PartyNotFoundException extends NotFoundException
{
    public function __construct(string $id)
    {
        parent::__construct("Party {$id} was not found.");
    }

    public function errorCode(): string
    {
        return 'party_not_found';
    }
}
