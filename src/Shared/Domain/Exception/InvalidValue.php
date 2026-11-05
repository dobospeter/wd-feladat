<?php

declare(strict_types=1);

namespace Inventory\Shared\Domain\Exception;

final class InvalidValue extends RuleViolationException
{
    public function __construct(private readonly string $field, string $message)
    {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return 'invalid_value';
    }

    public function details(): array
    {
        return [$this->field => [$this->getMessage()]];
    }
}
