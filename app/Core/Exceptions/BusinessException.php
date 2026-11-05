<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use RuntimeException;

/** Knows nothing about HTTP; the API layer maps the exception families to status codes. */
abstract class BusinessException extends RuntimeException
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
