<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\HttpFoundation\Response;

final class AssignRequestId
{
    public const ATTRIBUTE = 'request_id';
    public const HEADER = 'X-Request-Id';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/*')) {
            return $next($request);
        }

        $requestId = $this->resolveId($request);
        $request->attributes->set(self::ATTRIBUTE, $requestId);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set(self::HEADER, $requestId);

        return $response;
    }

    private function resolveId(Request $request): string
    {
        $incoming = $request->header(self::HEADER);

        if (is_string($incoming) && preg_match('/^[A-Za-z0-9._-]{8,64}$/', $incoming) === 1) {
            return $incoming;
        }

        return Uuid::v7()->toRfc4122();
    }
}
