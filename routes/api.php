<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/tasks', [TaskController::class, 'index']);
        Route::post('/tasks', [TaskController::class, 'store'])->middleware('can:create,App\Models\Task');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->middleware('can:update,task');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->middleware('can:destroy,task');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->middleware('can:view,task');
        Route::post('/tasks/{task}/assign', [TaskController::class, 'assign_task_to_user'])->middleware('can:assignTask,task');
        Route::post('/tasks/{task}/update-status', [TaskController::class, 'update_status'])->middleware('can:updateStatus,task');
        Route::post('/tasks/{task}/add-dependencies', [TaskController::class, 'add_dependencies'])->middleware('can:addDependencies,task');
    });
});
