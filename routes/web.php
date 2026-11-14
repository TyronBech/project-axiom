<?php

use App\Http\Controllers\DepartmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::apiResource('departments', DepartmentController::class)
    ->middleware('auth')
    ->only(['index', 'store', 'show', 'update', 'destroy']);
