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

    Route::resource('users', App\Http\Controllers\UserController::class);

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
    Route::resource('downtime-types', App\Http\Controllers\DowntimeTypeController::class);
    Route::resource('downtime-reasons', App\Http\Controllers\DowntimeReasonController::class);
    Route::resource('downtime-records', App\Http\Controllers\DowntimeRecordController::class);
    Route::resource('scrap-categories', App\Http\Controllers\ScrapCategoryController::class);
    Route::resource('scrap-reasons', App\Http\Controllers\ScrapReasonController::class);
    Route::resource('scrap-records', App\Http\Controllers\ScrapRecordController::class);
});

Route::get('production-records/entry-scan', [App\Http\Controllers\ProductionRecordController::class, 'entryScan'])->name('production-records.entry-scan');
Route::post('production-records/entry-scan', [App\Http\Controllers\ProductionRecordController::class, 'storeEntry'])->name('production-records.store-entry');

Route::get('production-records/exit-scan', [App\Http\Controllers\ProductionRecordController::class, 'exitScan'])->name('production-records.exit-scan');
Route::post('production-records/exit-scan', [App\Http\Controllers\ProductionRecordController::class, 'storeExit'])->name('production-records.store-exit');

Route::get('production-records/part-number-entry', [App\Http\Controllers\ProductionRecordController::class, 'partNumberEntry'])->name('production-records.part-number-entry');
Route::post('production-records/part-number-entry', [App\Http\Controllers\ProductionRecordController::class, 'storePartNumber'])->name('production-records.store-part-number');

Route::get('painting-process-tracking', function () {
    return view('guest.painting-process-tracking');
})->name('production-records.tracking');

Route::get('production-records-summary', function () {
    return view('guest.painting-production-summary');
})->name('production-records.summary');
