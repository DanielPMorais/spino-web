<?php

use App\Http\Controllers\Api\SensorCaptureController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/sensors/capture', SensorCaptureController::class)
    ->middleware('throttle:120,1')
    ->name('api.sensors.capture');
