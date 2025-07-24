<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/home', function () {
        return view('home');
    })->name('home');
});

Route::resource('customers', App\Http\Controllers\CustomerController::class);
Route::resource('projects', App\Http\Controllers\ProjectController::class);
Route::resource('project-prefixes', App\Http\Controllers\ProjectPrefixController::class);
Route::resource('item-classes', App\Http\Controllers\ItemClassController::class);
Route::resource('standard-packs', App\Http\Controllers\StandardPackController::class);
Route::resource('departments', App\Http\Controllers\DepartmentController::class);
Route::resource('areas', App\Http\Controllers\AreaController::class);
Route::resource('work-centers', App\Http\Controllers\WorkCenterController::class);
Route::resource('part-numbers', App\Http\Controllers\PartNumberController::class);
Route::resource('production-plans', App\Http\Controllers\ProductionPlanController::class);
