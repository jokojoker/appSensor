<?php

use App\Http\Controllers\Api\Sensors\TemperatureReadingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('sensors')->as('sensors:')->group(function(){
    Route::post('/receive', [TemperatureReadingController::class, 'receiveDataFromSensor'])->name('receivefromsensor');
    Route::get('/read', [TemperatureReadingController::class, 'checkDataFromSensor'])->name('checkfromsensor');
});
