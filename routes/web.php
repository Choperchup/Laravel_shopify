<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Auth;

// Route cho trang chính hiển thị sản phẩm
Route::get('/', [ProductController::class, 'index'])->name('home');

// API routes cho AJAX calls
Route::prefix('api')->group(function () {
    Route::get('/products', [ProductController::class, 'getProducts'])->name('api.products');
    Route::get('/shops', [ProductController::class, 'getShops'])->name('api.shops');
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
});
