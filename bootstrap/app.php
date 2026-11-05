<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inventory\ApiLog\Presentation\Http\Middleware\LogApiTraffic;
use Inventory\Shared\Domain\Exception\DomainException;
use Inventory\Shared\Presentation\Http\ApiExceptionRenderer;
use Inventory\Shared\Presentation\Http\Middleware\AssignRequestId;

// Routes are registered by the bounded context service providers.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The request id must be assigned before the traffic is logged.
        $middleware->append([
            AssignRequestId::class,
            LogApiTraffic::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport(DomainException::class);

        $exceptions->render(
            static fn (Throwable $e, Request $request) => $request->is('api/*')
                ? (new ApiExceptionRenderer())->render($e, $request)
                : null,
        );
    })->create();
