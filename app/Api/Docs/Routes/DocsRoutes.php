<?php

declare(strict_types=1);

use App\Api\Docs\Controllers\DocsSpecController;
use App\Api\Docs\Controllers\DocsUiController;
use App\Api\Docs\Controllers\ReadmeController;
use App\Api\Docs\Middleware\RequireDocsCredentials;
use Illuminate\Support\Facades\Route;

Route::get('/readme', ReadmeController::class)->name('docs.readme');
Route::redirect('/', '/readme');

Route::middleware(RequireDocsCredentials::class)->group(function (): void {
    Route::get('/docs', DocsUiController::class)->name('docs.ui');
    Route::get('/docs/openapi.yaml', DocsSpecController::class)->name('docs.spec');
});
