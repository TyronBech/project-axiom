<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::apiResource('departments', DepartmentController::class)
    ->middleware(['auth', EnsureUserIsActive::class])
    ->only(['index', 'store', 'show', 'update', 'destroy']);
