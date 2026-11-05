<?php

declare(strict_types=1);

namespace Inventory\Device\Infrastructure;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inventory\Device\Domain\DeviceRepository;
use Inventory\Device\Infrastructure\Persistence\EloquentDeviceRepository;

final class DeviceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DeviceRepository::class, EloquentDeviceRepository::class);
    }

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api/v1')
            ->group(__DIR__ . '/../Presentation/Http/routes.php');
    }
}
