<?php

namespace Tests\Unit\Core;

use App\Core\Exceptions\InvalidValueException;
use App\Core\Pagination\Page;
use App\Core\Pagination\PageRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PageRequestTest extends TestCase
{
    /**
     * @return array<string, array{int, int}>
     */
    public static function invalidRequests(): array
    {
        return ['page zero' => [0, 10], 'per page zero' => [1, 0], 'per page too large' => [1, 101]];
    }

    #[DataProvider('invalidRequests')]
    public function test_invalid_requests_are_rejected(int $page, int $perPage): void
    {
        $this->expectException(InvalidValueException::class);

        new PageRequest($page, $perPage);
    }

    public function test_the_last_page_is_at_least_one(): void
    {
        $this->assertSame(1, (new Page([], 0, new PageRequest()))->lastPage());
        $this->assertSame(3, (new Page([], 21, new PageRequest(1, 10)))->lastPage());
    }
}
