<?php

declare(strict_types=1);

namespace Inventory\Docs\Infrastructure;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inventory\Docs\Presentation\Http\Controller\DocsController;
use Inventory\Docs\Presentation\Http\Controller\ReadmeController;
use Inventory\Docs\Presentation\Http\Middleware\RequireDocsCredentials;

final class DocsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::get('/readme', [ReadmeController::class, 'show'])->name('docs.readme');
        Route::redirect('/', '/readme');

        Route::middleware(RequireDocsCredentials::class)->group(function (): void {
            Route::get('/docs', [DocsController::class, 'ui'])->name('docs.ui');
            Route::get('/docs/openapi.yaml', [DocsController::class, 'spec'])->name('docs.spec');
        });
    }
}
