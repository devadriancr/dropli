<?php

use App\Http\Controllers\ProductionReceiptReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScrapRecordController;
use App\Livewire\MaterialReceptionReport;

Route::get('/', function () {
    return redirect()->route('production-records.summary');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::resource('home',  App\Http\Controllers\HomeController::class);
    Route::resource('roles', App\Http\Controllers\RoleController::class)->except(['show']);
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

    Route::get('/production-records/pdf', [App\Http\Controllers\HomeController::class, 'productionRecordsPdf'])->name('home.production-records-pdf');

    Route::post('/production-plans/sync-all', [App\Http\Controllers\ProductionPlanController::class, 'syncAll'])->name('production-plans.sync-all');
});

Route::prefix('guest')->group(function () {
    Route::get('production-records-summary', function () {
        return view('guest.painting-production-summary');
    })->name('production-records.summary');

    Route::get('scrap-records/create', [ScrapRecordController::class, 'create'])
        ->name('guest.scrap-records.create');
    Route::post('scrap-records', [ScrapRecordController::class, 'store'])
        ->name('guest.scrap-records.store');

    Route::get('downtime-records/create', [App\Http\Controllers\DowntimeRecordController::class, 'create'])
        ->name('guest.downtime-records.create');
    Route::post('downtime-records', [App\Http\Controllers\DowntimeRecordController::class, 'store'])
        ->name('guest.downtime-records.store');
});

Route::prefix('production-records')->name('production-records.')->group(function () {
    // Entry Scan
    Route::get('entry-scan', [App\Http\Controllers\ProductionRecordController::class, 'entryScan'])->name('entry-scan');
    Route::post('entry-scan', [App\Http\Controllers\ProductionRecordController::class, 'storeEntry'])->name('store-entry');

    // Exit Scan
    Route::get('exit-scan', [App\Http\Controllers\ProductionRecordController::class, 'exitScan'])->name('exit-scan');
    Route::post('exit-scan', [App\Http\Controllers\ProductionRecordController::class, 'storeExit'])->name('store-exit');

    // Part Number Entry
    Route::get('part-number-entry', [App\Http\Controllers\ProductionRecordController::class, 'partNumberEntry'])->name('part-number-entry');
    Route::post('part-number-entry', [App\Http\Controllers\ProductionRecordController::class, 'storePartNumber'])->name('store-part-number');
});

Route::get('painting-process-tracking', function () {
    return view('guest.painting-process-tracking');
})->name('production-records.tracking');

Route::get('production-records-summary', function () {
    return view('guest.painting-production-summary');
})->name('production-records.summary');


Route::resource('test', ProductionReceiptReportController::class);
