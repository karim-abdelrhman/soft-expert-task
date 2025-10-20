<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::apiResource('/tasks', TaskController::class);

        Route::post('/tasks/{task}/assign', [TaskController::class, 'assign_task_to_user'])->middleware('can:assignTask,task');
        Route::patch('/tasks/{task}/update-status', [TaskController::class, 'update_status'])->middleware('can:updateStatus,task');
        Route::post('/tasks/{task}/add-dependencies', [TaskController::class, 'add_dependencies'])->middleware('can:addDependencies,task');
    });
});
