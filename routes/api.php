<?php

use App\Http\Controllers\Api\V1\AddressValidationController;
use App\Http\Controllers\Api\V1\CsvUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn() => response()->json(['status' => 'ok']));

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('csv-uploads')->name('csv-uploads.')->group(function () {
            Route::post('/', [CsvUploadController::class, 'upload'])->name('upload');
            Route::get('/', [CsvUploadController::class, 'index'])->name('index');
            Route::get('/{upload}', [CsvUploadController::class, 'show'])->name('show');
            Route::get('/{upload}/results', [AddressValidationController::class, 'results'])->name('results');
            Route::get('/{upload}/statistics', [AddressValidationController::class, 'statistics'])->name('statistics');
        });
    });
});
