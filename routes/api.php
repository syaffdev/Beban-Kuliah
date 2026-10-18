<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\AvailabilityExceptionsController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\TaskAnalysisController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// Auth

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Courses CRUD
Route::middleware('auth:sanctum')->prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::post('/', [CourseController::class, 'store']);
    Route::get('/{course}', [CourseController::class, 'show']);
    Route::put('/{course}', [CourseController::class, 'update']);
    Route::patch('/{course}', [CourseController::class, 'update']);
    Route::delete('/{course}', [CourseController::class, 'destroy']);
});

// Tasks CRUD
Route::middleware('auth:sanctum')->prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index']);
    Route::post('/', [TaskController::class, 'store']);
    Route::get('/{task}', [TaskController::class, 'show']);
    Route::put('/{task}', [TaskController::class, 'update']);
    Route::patch('/{task}', [TaskController::class, 'update']);
    Route::delete('/{task}', [TaskController::class, 'destroy']);
});

// Task Analysis
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/analysis/workload', [TaskAnalysisController::class, 'workload']);
    Route::get('/analysis/deadline-risk', [TaskAnalysisController::class, 'deadlineRisk']);
});

Route::middleware('auth:sanctum')->prefix('availability')->group(function () {
    Route::get('/weekly', [AvailabilityController::class, 'index']);
    Route::post('/weekly', [AvailabilityController::class, 'store']);
    Route::patch('/weekly/{id}', [AvailabilityController::class, 'update']);
    Route::delete('/weekly/{id}', [AvailabilityController::class, 'destroy']);
    Route::get('/exceptions', [AvailabilityExceptionsController::class, 'index']);
    Route::post('/exceptions', [AvailabilityExceptionsController::class, 'store']);
    Route::patch('/exceptions/{id}', [AvailabilityExceptionsController::class, 'update']);
    Route::delete('/exceptions/{id}', [AvailabilityExceptionsController::class, 'destroy']);

});
