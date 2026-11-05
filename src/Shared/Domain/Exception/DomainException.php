<?php

declare(strict_types=1);

namespace Inventory\Shared\Domain\Exception;

use RuntimeException;

/** The domain knows nothing about HTTP; the presentation layer maps the exception families to status codes. */
abstract class DomainException extends RuntimeException
{
    abstract public function errorCode(): string;

    /**
     * @return array<string, list<string>>
     */
    public function details(): array
    {
        return [];
    }
}
