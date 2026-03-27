<?php

use Illuminate\Support\Facades\Route;
use Webkul\Contracts\Http\Controllers\API\V1\AllocatablePoolController;

Route::name('admin.api.v1.contracts.')
    ->prefix('admin/api/v1/contracts')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('allocatable-pool', [AllocatablePoolController::class, 'index'])->name('allocatable-pool.index');
    });
