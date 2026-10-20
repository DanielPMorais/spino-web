<?php

use App\Http\Controllers\Api\ActiveTestRunController;
use App\Http\Controllers\Api\SensorCaptureController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/sensors/devices/{deviceCode}/active-run', ActiveTestRunController::class)
    ->middleware('throttle:60,1')
    ->name('api.sensors.active-run');

Route::post('/v1/sensors/capture', SensorCaptureController::class)
    ->middleware('throttle:120,1')
    ->name('api.sensors.capture');
