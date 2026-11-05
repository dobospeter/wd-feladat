<?php

declare(strict_types=1);

namespace Inventory\Shared\Presentation\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Inventory\Shared\Domain\Pagination\PageRequest;

final class PaginationRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:' . PageRequest::MAX_PER_PAGE],
        ];
    }

    public function toPageRequest(): PageRequest
    {
        return new PageRequest(
            (int) $this->validated('page', 1),
            (int) $this->validated('per_page', PageRequest::DEFAULT_PER_PAGE),
        );
    }
}
