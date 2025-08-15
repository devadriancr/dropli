<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('production-records.summary');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    Route::resource('customers', App\Http\Controllers\CustomerController::class)->except(['show']);
    Route::resource('projects', App\Http\Controllers\ProjectController::class)->except(['show']);
    Route::resource('project-prefixes', App\Http\Controllers\ProjectPrefixController::class)->except(['show']);
    Route::resource('item-classes', App\Http\Controllers\ItemClassController::class)->except(['show']);
    Route::resource('standard-packs', App\Http\Controllers\StandardPackController::class)->except(['show']);
    Route::resource('departments', App\Http\Controllers\DepartmentController::class)->except(['show']);
    Route::resource('areas', App\Http\Controllers\AreaController::class)->except(['show']);
    Route::resource('work-centers', App\Http\Controllers\WorkCenterController::class)->except(['show']);
    Route::resource('part-numbers', App\Http\Controllers\PartNumberController::class)->except(['show']);
    Route::resource('shifts', App\Http\Controllers\ShiftController::class)->except(['show']);
    Route::resource('production-plans', App\Http\Controllers\ProductionPlanController::class)->except(['show']);
    Route::resource('production-records', App\Http\Controllers\ProductionRecordController::class)->except(['show']);
});

Route::get('production-records/label-scan', [App\Http\Controllers\ProductionRecordController::class, 'scanLabel'])->name('production-records.scan-label');
Route::post('production-records/label-scan', [App\Http\Controllers\ProductionRecordController::class, 'storeLabel'])->name('production-records.store-label');

Route::get('painting-process-tracking', function () {
    return view('guest.painting-process-tracking');
})->name('production-records.tracking');

Route::get('production-records-summary', function () {
    return view('guest.painting-production-summary');
})->name('production-records.summary');
