<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Pagination\Page;
use PHPUnit\Framework\TestCase;
use App\Core\Pagination\PageRequest;
use App\Core\Exceptions\InvalidValueException;
use PHPUnit\Framework\Attributes\DataProvider;

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
    public function testInvalidRequestsAreRejected(int $page, int $perPage): void
    {
        $this->expectException(InvalidValueException::class);

        new PageRequest($page, $perPage);
    }

    public function testTheLastPageIsAtLeastOne(): void
    {
        $this->assertSame(1, (new Page([], 0, new PageRequest()))->lastPage());
        $this->assertSame(3, (new Page([], 21, new PageRequest(1, 10)))->lastPage());
    }
}
