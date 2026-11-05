<?php

declare(strict_types=1);

use App\Api\Party\Controllers\PartyCreateController;
use App\Api\Party\Controllers\PartyDeleteController;
use App\Api\Party\Controllers\PartyListController;
use App\Api\Party\Controllers\PartyShowController;
use App\Api\Party\Controllers\PartyUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('parties', PartyListController::class)->name('parties.index');
Route::post('parties', PartyCreateController::class)->name('parties.store');
Route::get('parties/{party}', PartyShowController::class)->whereUuid('party')->name('parties.show');
Route::put('parties/{party}', PartyUpdateController::class)->whereUuid('party')->name('parties.update');
Route::delete('parties/{party}', PartyDeleteController::class)->whereUuid('party')->name('parties.destroy');
