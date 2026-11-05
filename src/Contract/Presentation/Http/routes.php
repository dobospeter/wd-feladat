<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inventory\Contract\Presentation\Http\Controller\ContractController;
use Inventory\Contract\Presentation\Http\Controller\ContractDeviceController;

// Updates are full replacements: PUT only, no PATCH.
Route::apiResource('contracts', ContractController::class)->except('update')->whereUuid('contract');
Route::put('contracts/{contract}', [ContractController::class, 'update'])->whereUuid('contract')->name('contracts.update');

Route::post('contracts/{contract}/devices', [ContractDeviceController::class, 'store'])
    ->whereUuid('contract')
    ->name('contracts.devices.store');

Route::delete('contracts/{contract}/devices/{assignment}', [ContractDeviceController::class, 'destroy'])
    ->whereUuid(['contract', 'assignment'])
    ->name('contracts.devices.destroy');
