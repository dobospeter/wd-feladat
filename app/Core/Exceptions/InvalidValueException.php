<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Override;

final class InvalidValueException extends RuleViolationException
{
    public function __construct(private readonly string $field, string $message)
    {
        parent::__construct($message);
    }

    #[Override]
    public function errorCode(): string
    {
        return 'invalid_value';
    }

    #[Override]
    public function details(): array
    {
        return [$this->field => [$this->getMessage()]];
    }
}
