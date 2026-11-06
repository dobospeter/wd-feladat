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
    public static function validRequests(): array
    {
        return [
            'defaults' => [1, PageRequest::DEFAULT_PER_PAGE],
            'smallest values' => [1, 1],
            'largest per page' => [1, PageRequest::MAX_PER_PAGE],
            'far away page' => [9999, 10],
        ];
    }

    /**
     * @return array<string, array{int, int}>
     */
    public static function invalidRequests(): array
    {
        return [
            'page zero' => [0, 10],
            'negative page' => [-1, 10],
            'per page zero' => [1, 0],
            'negative per page' => [1, -5],
            'per page too large' => [1, PageRequest::MAX_PER_PAGE + 1],
        ];
    }

    #[DataProvider('validRequests')]
    public function testValidRequestsAreAccepted(int $page, int $perPage): void
    {
        $request = new PageRequest($page, $perPage);

        $this->assertSame($page, $request->page);
        $this->assertSame($perPage, $request->perPage);
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
