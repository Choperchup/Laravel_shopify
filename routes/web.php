<?php

use App\Http\Controllers\ProductGraphQLController;
use App\Http\Controllers\RulesGraphQLController;
use Doctrine\Inflector\Rules\English\Rules;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Route;

Route::middleware(['ensure.host'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    Route::get('/products', [ProductGraphQLController::class, 'index'])
        ->name('product.index');

    Route::get('/orders', function () {
        return view('order');
    })->name('orders.index');

    Route::post('/products/bulk-action', [ProductGraphQLController::class, 'bulkAction'])
        ->name('products.bulk-action');


    Route::get('/rules', [RulesGraphQLController::class, 'index'])
        ->name('rules.index');

    Route::get('rules/create', [RulesGraphQLController::class, 'create'])
        ->name('rules.create');

    Route::post('rules', [RulesGraphQLController::class, 'store'])
        ->name('rules.store');

    Route::get('rules/{rule}/edit', [RulesGraphQLController::class, 'edit'])
        ->name('rules.edit');

    Route::put('rules/{rule}', [RulesGraphQLController::class, 'update'])
        ->name('rules.update');

    Route::delete('rules/{rule}', [RulesGraphQLController::class, 'destroy'])
        ->name('rules.destroy');


    // Tìm kiếm API
    Route::get('/products/search', [RulesGraphQLController::class, 'searchProducts'])->name('api.products.search');
    // Thêm các route khác (cần triển khai phương thức tương ứng)
    Route::get('/collections/search', [RulesGraphQLController::class, 'searchCollections'])->name('api.collections.search');
    Route::get('/tags/search', [RulesGraphQLController::class, 'searchTags'])->name('api.tags.search');
    Route::get('/vendors/search', [RulesGraphQLController::class, 'searchVendors'])->name('api.vendors.search');
});

Route::get('/products/bulk-action/status/{batchId}', function ($batchId) {
    $batch = Bus::findBatch($batchId);
    if (!$batch) {
        return response()->json(['error' => 'Batch not found']);
    }

    logger("🔎 Batch progress: " . $batch->progress());

    return response()->json([
        'finished' => $batch->finished(),
        'progress' => $batch->progress(),
        'failed'   => $batch->hasFailures(),
    ]);
});
