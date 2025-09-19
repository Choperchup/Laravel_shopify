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
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
    return view('welcome');
})->middleware(['verify.shopify'])->name('home');



Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
