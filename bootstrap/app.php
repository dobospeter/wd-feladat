<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Api\ApiLog\Middleware\LogApiTraffic;
use App\Core\Exceptions\BusinessException;
use App\Core\Http\ApiExceptionRenderer;
use App\Core\Http\Middleware\AssignRequestId;

// Routes are loaded from app/Api/*/Routes by RouteServiceProvider.
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
        $exceptions->dontReport(BusinessException::class);

        $exceptions->render(
            static fn (Throwable $e, Request $request) => $request->is('api/*')
                ? (new ApiExceptionRenderer())->render($e, $request)
                : null,
        );
    })->create();
