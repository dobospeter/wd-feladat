<?php

declare(strict_types=1);

namespace Inventory\ApiLog\Application;

use DateTimeImmutable;

final readonly class RawExchange
{
    /**
     * @param array<string, mixed> $requestHeaders
     * @param array<string, mixed> $responseHeaders
     */
    public function __construct(
        public ?string $requestId,
        public string $method,
        public string $path,
        public string $uri,
        public ?string $ip,
        public ?string $userAgent,
        public array $requestHeaders,
        public ?string $requestBody,
        public int $responseStatus,
        public array $responseHeaders,
        public ?string $responseBody,
        public int $durationMs,
        public DateTimeImmutable $occurredAt,
    ) {
    }
}
