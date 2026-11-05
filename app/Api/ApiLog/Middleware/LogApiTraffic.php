<?php

declare(strict_types=1);

namespace App\Api\ApiLog\Middleware;

use Closure;
use DateTimeImmutable;
use Illuminate\Http\Request;
use App\Core\Http\Middleware\AssignRequestId;
use App\Modules\ApiLog\Handlers\ApiExchangeRecordHandler;
use App\Modules\ApiLog\Handlers\Payloads\ApiExchangePayload;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/** Global so that unmatched routes (404/405) are logged too; written in terminate() to add no latency. */
final class LogApiTraffic
{
    private const STARTED_AT = 'api_log.started_at';

    public function __construct(private readonly ApiExchangeRecordHandler $recorder)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            $request->attributes->set(self::STARTED_AT, hrtime(true));
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (!$request->attributes->has(self::STARTED_AT)) {
            return;
        }

        try {
            $this->recorder->handle($this->snapshot($request, $response));
        } catch (Throwable $e) {
            // Logging must never turn a successful API call into a failure.
            report($e);
        }
    }

    private function snapshot(Request $request, Response $response): ApiExchangePayload
    {
        $elapsedNs = hrtime(true) - (int) $request->attributes->get(self::STARTED_AT);
        $body = $response->getContent();

        return new ApiExchangePayload(
            $request->attributes->get(AssignRequestId::ATTRIBUTE),
            $request->method(),
            '/' . ltrim($request->path(), '/'),
            $request->fullUrl(),
            $request->ip(),
            $request->userAgent(),
            $request->headers->all(),
            $request->getContent(),
            $response->getStatusCode(),
            $response->headers->all(),
            $body === false ? null : $body,
            intdiv($elapsedNs, 1_000_000),
            new DateTimeImmutable(),
        );
    }
}
