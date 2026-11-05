<?php

declare(strict_types=1);

namespace Inventory\Shared\Domain;

use Inventory\Shared\Domain\Exception\InvalidValue;

final class Guard
{
    private function __construct()
    {
    }

    /** Trimmed, non-blank, at most $maxLength characters. */
    public static function text(string $value, string $field, int $maxLength): string
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidValue($field, "The {$field} must not be blank.");
        }

        return self::maxLength($value, $field, $maxLength);
    }

    /** Like text(), but a blank value becomes null. */
    public static function optionalText(?string $value, string $field, int $maxLength): ?string
    {
        $value = $value === null ? '' : trim($value);

        return $value === '' ? null : self::maxLength($value, $field, $maxLength);
    }

    private static function maxLength(string $value, string $field, int $maxLength): string
    {
        if (mb_strlen($value) > $maxLength) {
            throw new InvalidValue($field, "The {$field} must not be longer than {$maxLength} characters.");
        }

        return $value;
    }
}
