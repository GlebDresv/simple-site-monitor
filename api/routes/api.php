<?php

use App\Http\Controllers\CheckLogController;
use App\Http\Controllers\CheckSettingController;
use App\Http\Controllers\ConstantsController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\NotificationSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/constants', [ConstantsController::class, 'index']);
    Route::apiResource('notification-settings', NotificationSettingController::class);
    Route::apiResource('check-settings', CheckSettingController::class);
    Route::apiResource('domains', DomainController::class);
    Route::apiResource('check-logs', CheckLogController::class)->only(['index', 'show']);
});
