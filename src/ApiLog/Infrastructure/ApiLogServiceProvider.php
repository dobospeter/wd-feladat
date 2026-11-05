<?php

declare(strict_types=1);

namespace Inventory\ApiLog\Infrastructure;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Inventory\ApiLog\Application\PayloadSanitizer;
use Inventory\ApiLog\Domain\ApiLogRepository;
use Inventory\ApiLog\Infrastructure\Persistence\EloquentApiLogRepository;

final class ApiLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ApiLogRepository::class, EloquentApiLogRepository::class);

        $this->app->bind(PayloadSanitizer::class, static fn (Application $app): PayloadSanitizer => new PayloadSanitizer(
            (int) $app['config']->get('api_log.max_body_bytes'),
            $app['config']->get('api_log.redacted_headers'),
            $app['config']->get('api_log.redacted_fields'),
        ));
    }
}
