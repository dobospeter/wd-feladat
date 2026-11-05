<?php

declare(strict_types=1);

namespace App\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Loads app/Api/<Module>/Routes/*Routes.php: REST modules under /api/v1, documentation modules at the root.
 */
final class RouteServiceProvider extends ServiceProvider
{
    private const array DOCUMENTATION_MODULES = ['Docs'];

    public function boot(): void
    {
        foreach (glob(app_path('Api/*/Routes/*Routes.php')) ?: [] as $file) {
            if (in_array(basename(dirname($file, 2)), self::DOCUMENTATION_MODULES, true)) {
                Route::group([], $file);

                continue;
            }

            Route::middleware('api')->prefix('api/v1')->group($file);
        }
    }
}
