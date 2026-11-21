<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::apiResource('departments', DepartmentController::class)
    ->middleware(['auth:sanctum', EnsureUserIsActive::class])
    ->only(['index', 'store', 'show', 'update', 'destroy']);

Route::apiResource('users', UserController::class)
    ->middleware(['auth:sanctum', EnsureUserIsActive::class])
    ->only(['index', 'store', 'show', 'update', 'destroy']);
