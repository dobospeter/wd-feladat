<?php

declare(strict_types=1);

namespace Inventory\ApiLog\Infrastructure\Persistence;

use Inventory\ApiLog\Domain\ApiLogEntry;
use Inventory\ApiLog\Domain\ApiLogRepository;

final class EloquentApiLogRepository implements ApiLogRepository
{
    public function save(ApiLogEntry $entry): void
    {
        ApiLogModel::query()->create([
            'request_id' => $entry->requestId,
            'method' => $entry->method,
            'path' => $entry->path,
            'uri' => $entry->uri,
            'ip' => $entry->ip,
            'user_agent' => $entry->userAgent,
            'request_headers' => $entry->requestHeaders,
            'request_body' => $entry->requestBody,
            'response_status' => $entry->responseStatus,
            'response_headers' => $entry->responseHeaders,
            'response_body' => $entry->responseBody,
            'duration_ms' => $entry->durationMs,
            'created_at' => $entry->occurredAt->format('Y-m-d H:i:s.u'),
        ]);
    }
}
