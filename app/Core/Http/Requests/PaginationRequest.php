<?php

declare(strict_types=1);

namespace App\Core\Http\Requests;

use App\Core\Pagination\PageRequest;
use Illuminate\Foundation\Http\FormRequest;

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
            $this->integer('page', 1),
            $this->integer('per_page', PageRequest::DEFAULT_PER_PAGE),
        );
    }
}
