<?php

use App\Http\Controllers\Api\V1\AddressValidationController;
use App\Http\Controllers\Api\V1\CsvUploadController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/health', fn() => response()->json(['status' => 'ok']));

// API V1 Routes
Route::prefix('v1')->name('api.v1.')->group(function () {
    
    // Protected routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // CSV Upload Management
        Route::prefix('csv-uploads')->name('csv-uploads.')->group(function () {
            Route::post('/', [CsvUploadController::class, 'upload'])->name('upload');
            Route::get('/', [CsvUploadController::class, 'index'])->name('index');
            Route::get('/{upload}', [CsvUploadController::class, 'show'])->name('show');
            
            // Validation Results (nested resource)
            Route::get('/{upload}/results', [AddressValidationController::class, 'results'])->name('results');
            Route::get('/{upload}/statistics', [AddressValidationController::class, 'statistics'])->name('statistics');
        });
    });
});
