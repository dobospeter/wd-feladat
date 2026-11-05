<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inventory\Device\Presentation\Http\Controller\DeviceController;

// Updates are full replacements: PUT only, no PATCH.
Route::apiResource('devices', DeviceController::class)->except('update')->whereUuid('device');
Route::put('devices/{device}', [DeviceController::class, 'update'])->whereUuid('device')->name('devices.update');
