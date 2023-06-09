<?php

use App\Http\Controllers\Api\Sensors\TemperatureReadingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/',[TemperatureReadingController::class, 'index']);
Route::get('data', [TemperatureReadingController::class, 'fakeSensorCSV']);
Route::get('getavgfromall', [TemperatureReadingController::class, 'getAvarageFromAllSensors']);
Route::get('getavgfromone', [TemperatureReadingController::class, 'getAverageFromSensor']);

