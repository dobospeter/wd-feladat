<?php

declare(strict_types=1);

namespace App\Modules\ApiLog\Handlers;

use App\Modules\ApiLog\Support\PayloadSanitizer;
use App\Modules\ApiLog\Repositories\ApiLogRepository;
use App\Modules\ApiLog\Handlers\Payloads\ApiExchangePayload;

final readonly class ApiExchangeRecordHandler
{
    public function __construct(
        private PayloadSanitizer $sanitizer,
        private ApiLogRepository $logs,
    ) {
    }

    public function handle(ApiExchangePayload $exchange): void
    {
        $this->logs->create([
            'request_id' => $exchange->requestId,
            'method' => $exchange->method,
            'path' => $exchange->path,
            'uri' => $exchange->uri,
            'ip' => $exchange->ip,
            'user_agent' => $exchange->userAgent,
            'request_headers' => $this->sanitizer->headers($exchange->requestHeaders),
            'request_body' => $this->sanitizer->body($exchange->requestBody, $this->contentType($exchange->requestHeaders)),
            'response_status' => $exchange->responseStatus,
            'response_headers' => $this->sanitizer->headers($exchange->responseHeaders),
            'response_body' => $this->sanitizer->body($exchange->responseBody, $this->contentType($exchange->responseHeaders)),
            'duration_ms' => $exchange->durationMs,
            'created_at' => $exchange->occurredAt->format('Y-m-d H:i:s.u'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $headers
     */
    private function contentType(array $headers): ?string
    {
        foreach ($headers as $name => $value) {
            if (strtolower((string) $name) !== 'content-type') {
                continue;
            }

            $first = is_array($value) ? reset($value) : $value;

            return is_string($first) ? $first : null;
        }

        return null;
    }
}
