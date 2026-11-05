<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Api\Device\Controllers\DeviceListController;
use App\Api\Device\Controllers\DeviceShowController;
use App\Api\Device\Controllers\DeviceCreateController;
use App\Api\Device\Controllers\DeviceDeleteController;
use App\Api\Device\Controllers\DeviceUpdateController;

Route::get('devices', DeviceListController::class)->name('devices.index');
Route::post('devices', DeviceCreateController::class)->name('devices.store');
Route::get('devices/{device}', DeviceShowController::class)->whereUuid('device')->name('devices.show');
Route::put('devices/{device}', DeviceUpdateController::class)->whereUuid('device')->name('devices.update');
Route::delete('devices/{device}', DeviceDeleteController::class)->whereUuid('device')->name('devices.destroy');
