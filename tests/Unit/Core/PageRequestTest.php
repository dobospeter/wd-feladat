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
        // Arrange: the page and per-page values come from the data provider.

        // Act
        $request = new PageRequest($page, $perPage);

        // Assert
        $this->assertSame($page, $request->page);
        $this->assertSame($perPage, $request->perPage);
    }

    #[DataProvider('invalidRequests')]
    public function testInvalidRequestsAreRejected(int $page, int $perPage): void
    {
        // Arrange
        $this->expectException(InvalidValueException::class);

        // Act
        new PageRequest($page, $perPage);

        // Assert: the expected exception is verified by PHPUnit.
    }

    public function testTheLastPageIsAtLeastOne(): void
    {
        // Arrange
        $emptyPage = new Page([], 0, new PageRequest());
        $partiallyFilledLastPage = new Page([], 21, new PageRequest(1, 10));

        // Act
        $emptyLastPage = $emptyPage->lastPage();
        $filledLastPage = $partiallyFilledLastPage->lastPage();

        // Assert
        $this->assertSame(1, $emptyLastPage);
        $this->assertSame(3, $filledLastPage);
    }
}
