<?php

declare(strict_types=1);

namespace Inventory\ApiLog\Application;

use Inventory\ApiLog\Domain\ApiLogEntry;
use Inventory\ApiLog\Domain\ApiLogRepository;

final readonly class RecordApiExchange
{
    public function __construct(
        private PayloadSanitizer $sanitizer,
        private ApiLogRepository $logs,
    ) {
    }

    public function handle(RawExchange $exchange): void
    {
        $this->logs->save(new ApiLogEntry(
            $exchange->requestId,
            $exchange->method,
            $exchange->path,
            $exchange->uri,
            $exchange->ip,
            $exchange->userAgent,
            $this->sanitizer->headers($exchange->requestHeaders),
            $this->sanitizer->body($exchange->requestBody, $this->contentType($exchange->requestHeaders)),
            $exchange->responseStatus,
            $this->sanitizer->headers($exchange->responseHeaders),
            $this->sanitizer->body($exchange->responseBody, $this->contentType($exchange->responseHeaders)),
            $exchange->durationMs,
            $exchange->occurredAt,
        ));
    }

    /**
     * @param array<string, mixed> $headers
     */
    private function contentType(array $headers): ?string
    {
        foreach ($headers as $name => $value) {
            if (strtolower((string) $name) === 'content-type') {
                return (string) (is_array($value) ? ($value[0] ?? '') : $value);
            }
        }

        return null;
    }
}
