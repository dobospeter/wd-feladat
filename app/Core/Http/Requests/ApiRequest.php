<?php

declare(strict_types=1);

namespace App\Core\Http\Requests;

use LogicException;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Typed access to validated input; the validation rules guarantee the types read here.
 */
abstract class ApiRequest extends FormRequest
{
    protected function nullableString(string $key): ?string
    {
        $value = $this->validated($key);

        return is_string($value) ? $value : null;
    }

    protected function requiredString(string $key): string
    {
        return $this->nullableString($key) ?? throw new LogicException("Validated field '{$key}' is missing.");
    }
}
