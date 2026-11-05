<?php

declare(strict_types=1);

namespace Inventory\Shared\Presentation\Http;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inventory\Shared\Domain\Exception\ConflictException;
use Inventory\Shared\Domain\Exception\DomainException;
use Inventory\Shared\Domain\Exception\NotFoundException;
use Inventory\Shared\Presentation\Http\Middleware\AssignRequestId;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ApiExceptionRenderer
{
    public function render(Throwable $exception, Request $request): JsonResponse
    {
        $error = $this->describe($exception);

        $body = [
            'code' => $error->code,
            'message' => $error->message,
        ];

        if ($error->details !== []) {
            $body['details'] = $error->details;
        }

        $requestId = $request->attributes->get(AssignRequestId::ATTRIBUTE);
        if ($requestId !== null) {
            $body['request_id'] = $requestId;
        }

        return new JsonResponse(['error' => $body], $error->status, $error->headers);
    }

    private function describe(Throwable $e): ApiError
    {
        return match (true) {
            $e instanceof ValidationException => new ApiError(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'validation_failed',
                'The given data was invalid.',
                $e->errors(),
            ),
            $e instanceof DomainException => new ApiError(
                $this->domainStatus($e),
                $e->errorCode(),
                $e->getMessage(),
                $e->details(),
            ),
            $e instanceof UniqueConstraintViolationException => new ApiError(
                Response::HTTP_CONFLICT,
                'conflict',
                'The request conflicts with existing data.',
            ),
            $e instanceof ModelNotFoundException => $this->notFound(),
            $e instanceof AuthenticationException => new ApiError(
                Response::HTTP_UNAUTHORIZED,
                'unauthenticated',
                'Authentication is required.',
            ),
            $e instanceof AuthorizationException => new ApiError(
                Response::HTTP_FORBIDDEN,
                'forbidden',
                'This action is forbidden.',
            ),
            $e instanceof RequestExceptionInterface => new ApiError(
                Response::HTTP_BAD_REQUEST,
                'bad_request',
                'The request could not be understood.',
            ),
            $e instanceof HttpExceptionInterface => $this->http($e),
            default => new ApiError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'server_error',
                'An unexpected error occurred.',
            ),
        };
    }

    private function domainStatus(DomainException $e): int
    {
        return match (true) {
            $e instanceof NotFoundException => Response::HTTP_NOT_FOUND,
            $e instanceof ConflictException => Response::HTTP_CONFLICT,
            default => Response::HTTP_UNPROCESSABLE_ENTITY,
        };
    }

    private function http(HttpExceptionInterface $e): ApiError
    {
        $status = $e->getStatusCode();

        if ($status === Response::HTTP_NOT_FOUND) {
            return $this->notFound();
        }

        [$code, $message] = match ($status) {
            Response::HTTP_BAD_REQUEST => ['bad_request', 'The request could not be understood.'],
            Response::HTTP_METHOD_NOT_ALLOWED => ['method_not_allowed', 'The HTTP method is not allowed for this resource.'],
            Response::HTTP_UNSUPPORTED_MEDIA_TYPE => ['unsupported_media_type', 'The media type is not supported.'],
            Response::HTTP_TOO_MANY_REQUESTS => ['too_many_requests', 'Too many requests.'],
            default => [
                $status >= 500 ? 'server_error' : 'http_error',
                $status >= 500 ? 'An unexpected error occurred.' : (Response::$statusTexts[$status] ?? 'HTTP error.'),
            ],
        };

        return new ApiError($status, $code, $message, headers: $this->safeHeaders($e->getHeaders()));
    }

    private function notFound(): ApiError
    {
        return new ApiError(Response::HTTP_NOT_FOUND, 'not_found', 'The requested resource was not found.');
    }

    /**
     * @param array<string, mixed> $headers
     * @return array<string, string>
     */
    private function safeHeaders(array $headers): array
    {
        $allowed = ['allow', 'retry-after', 'x-ratelimit-limit', 'x-ratelimit-remaining'];

        $safe = [];
        foreach ($headers as $name => $value) {
            if (in_array(strtolower((string) $name), $allowed, true)) {
                $safe[$name] = (string) $value;
            }
        }

        return $safe;
    }
}
