<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Api\Contract\Controllers\ContractListController;
use App\Api\Contract\Controllers\ContractShowController;
use App\Api\Contract\Controllers\ContractCreateController;
use App\Api\Contract\Controllers\ContractDeleteController;
use App\Api\Contract\Controllers\ContractUpdateController;
use App\Api\Contract\Controllers\ContractDeviceAttachController;
use App\Api\Contract\Controllers\ContractDeviceDetachController;

Route::get('contracts', ContractListController::class)->name('contracts.index');
Route::post('contracts', ContractCreateController::class)->name('contracts.store');
Route::get('contracts/{contract}', ContractShowController::class)->whereUuid('contract')->name('contracts.show');
Route::put('contracts/{contract}', ContractUpdateController::class)->whereUuid('contract')->name('contracts.update');
Route::delete('contracts/{contract}', ContractDeleteController::class)->whereUuid('contract')->name('contracts.destroy');

Route::post('contracts/{contract}/devices', ContractDeviceAttachController::class)
    ->whereUuid('contract')
    ->name('contracts.devices.store');

Route::delete('contracts/{contract}/devices/{assignment}', ContractDeviceDetachController::class)
    ->whereUuid(['contract', 'assignment'])
    ->name('contracts.devices.destroy');
