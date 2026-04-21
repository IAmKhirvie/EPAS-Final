<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnnouncementController;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/dashboard/progress', [DashboardController::class, 'getProgressData']);
    Route::get('/dashboard/progress-report', [DashboardController::class, 'getProgressReport']);
});