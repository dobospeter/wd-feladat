<?php

declare(strict_types=1);

namespace App\Modules\Contract\Exceptions;

use Override;
use App\Core\Exceptions\RuleViolationException;

/** Reported as 422 rather than 404: the party is referenced in the request body, not addressed by the URL. */
final class PartyNotAvailableException extends RuleViolationException
{
    public function __construct(string $id)
    {
        parent::__construct("Party {$id} does not exist.");
    }

    #[Override]
    public function errorCode(): string
    {
        return 'party_not_found';
    }

    #[Override]
    public function details(): array
    {
        return ['party_id' => [$this->getMessage()]];
    }
}
