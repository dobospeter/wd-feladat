<?php

declare(strict_types=1);

namespace App\Core\Pagination;

/**
 * @template-covariant T
 */
final readonly class Page
{
    /**
     * @param  list<T>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public PageRequest $request,
    ) {
    }

    public function lastPage(): int
    {
        return max(1, (int) ceil($this->total / $this->request->perPage));
    }
}
