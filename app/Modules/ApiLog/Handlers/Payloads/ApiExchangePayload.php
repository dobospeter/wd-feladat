<?php

declare(strict_types=1);

namespace App\Modules\ApiLog\Handlers\Payloads;

use DateTimeImmutable;

final readonly class ApiExchangePayload
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
