<?php

declare(strict_types=1);

namespace App\Core\Pagination;

use App\Core\Exceptions\InvalidValueException;

final readonly class PageRequest
{
    public const int DEFAULT_PER_PAGE = 15;
    public const int MAX_PER_PAGE = 100;

    public function __construct(
        public int $page = 1,
        public int $perPage = self::DEFAULT_PER_PAGE,
    ) {
        if ($page < 1) {
            throw new InvalidValueException('page', 'The page must be at least 1.');
        }

        if ($perPage < 1 || $perPage > self::MAX_PER_PAGE) {
            throw new InvalidValueException(
                'per_page',
                'The per_page must be between 1 and ' . self::MAX_PER_PAGE . '.',
            );
        }
    }
}
