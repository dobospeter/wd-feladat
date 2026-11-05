<?php

declare(strict_types=1);

namespace App\Core\Pagination;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    /**
     * @template TModel
     * @template TResult
     *
     * @param  LengthAwarePaginator<int, TModel>  $paginator
     * @param  callable(TModel): TResult  $map
     * @return Page<TResult>
     */
    public static function fromPaginator(LengthAwarePaginator $paginator, PageRequest $request, callable $map): self
    {
        return new self(array_values(array_map($map, $paginator->items())), $paginator->total(), $request);
    }

    public function lastPage(): int
    {
        return max(1, (int) ceil($this->total / $this->request->perPage));
    }
}
