<?php

use App\Http\Controllers\ProductGraphQLController;
use App\Http\Controllers\RulesGraphQLController;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Route;
use App\Models\Rule;

// Nhóm route của app
Route::middleware(['ensure.host', 'ensure.hmac'])->group(function () {
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


    Route::post('rules/{rule}/duplicate', [RulesGraphQLController::class, 'duplicate'])
        ->name('rules.duplicate');

    Route::post('rules/{rule}/archive', [RulesGraphQLController::class, 'archive'])
        ->name('rules.archive');

    Route::post('rules/{rule}/toggle', [RulesGraphQLController::class, 'toggle'])
        ->name('rules.toggle');

    Route::post('rules/{rule}/restore', [RulesGraphQLController::class, 'restore'])
        ->name('rules.restore');
    // Search APIs
    Route::get('/products/search', [RulesGraphQLController::class, 'searchProducts'])
        ->name('api.products.search');
    Route::get('/collections/search', [RulesGraphQLController::class, 'searchCollections'])
        ->name('api.collections.search');
    Route::get('/tags/search', [RulesGraphQLController::class, 'searchTags'])
        ->name('api.tags.search');
    Route::get('/vendors/search', [RulesGraphQLController::class, 'searchVendors'])
        ->name('api.vendors.search');
});

// Route check batch status (không cần verify hmac, dùng cho job tracking nội bộ)
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


// Route này trả về dữ liệu JSON của một rule để JavaScript kiểm tra
Route::get('/rules/{rule}/status-json', function (Rule $rule) {
    $batch = $rule->job_batch_id ? Bus::findBatch($rule->job_batch_id) : null;

    if ($batch) {
        // Cập nhật số sản phẩm đã xử lý từ batch job
        $rule->update(['processed_products' => $batch->processedJobs()]);
    }
    return response()->json($rule->fresh());
})->name('rules.status.json');

// Route này trả về HTML của riêng ô trạng thái để cập nhật giao diện
Route::get('/rules/{rule}/status-display', function (Rule $rule) {
    return view('rules.partials._status_display', ['rule' => $rule->fresh()]);
})->name('rules.status.display');
