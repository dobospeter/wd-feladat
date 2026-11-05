<?php

declare(strict_types=1);

namespace Inventory\Contract\Infrastructure;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inventory\Contract\Domain\ContractRepository;
use Inventory\Contract\Infrastructure\Persistence\EloquentContractRepository;
use Inventory\Contract\Infrastructure\Persistence\EloquentDeviceUsageChecker;
use Inventory\Device\Domain\DeviceUsageChecker;

final class ContractServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContractRepository::class, EloquentContractRepository::class);
        $this->app->bind(DeviceUsageChecker::class, EloquentDeviceUsageChecker::class);
    }

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api/v1')
            ->group(__DIR__ . '/../Presentation/Http/routes.php');
    }
}
