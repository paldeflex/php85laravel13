<?php

use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HealthCheckController::class, '__invoke']);
Route::get('/health-check/run', [HealthCheckController::class, 'runCheck']);
