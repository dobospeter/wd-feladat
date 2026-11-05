<?php

declare(strict_types=1);

namespace Inventory\Contract\Domain;

use Inventory\Shared\Domain\Guard;
use Stringable;

final readonly class SerialNumber implements Stringable
{
    public const MAX_LENGTH = 100;

    private function __construct(public string $value)
    {
    }

    public static function fromString(string $value): self
    {
        return new self(Guard::text($value, 'serial_number', self::MAX_LENGTH));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
