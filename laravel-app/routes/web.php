<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReturnController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest:web,admin')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/login', 'auth.admin-login')->name('login');
    });

Route::middleware('auth:admin')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/users', [AdminDashboardController::class, 'users'])
            ->name('users');
        Route::get('/categories', [AdminDashboardController::class, 'categories'])
            ->name('categories');
        Route::get('/products', [AdminProductsController::class, 'index'])
            ->name('products');
        Route::post('/products', [AdminProductsController::class, 'store'])
            ->name('products.store');
        Route::patch('/products/{productId}', [AdminProductsController::class, 'update'])
            ->whereNumber('productId')
            ->name('products.update');
        Route::delete('/products/{productId}', [AdminProductsController::class, 'destroy'])
            ->whereNumber('productId')
            ->name('products.destroy');
        Route::get('/orders', [AdminDashboardController::class, 'orders'])
            ->name('orders');
        Route::get('/returns', [AdminDashboardController::class, 'returns'])
            ->name('returns');
    });

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');
    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('verified')
        ->name('products');
    Route::post('/cart/add/{productId}', [CartController::class, 'add'])
        ->middleware('verified')
        ->name('cart.add');
    Route::post('/cart/remove/{productId}', [CartController::class, 'remove'])
        ->middleware('verified')
        ->name('cart.remove');
    Route::get('/checkout', [CheckoutController::class, 'show'])
        ->middleware('verified')
        ->name('checkout.show');
    Route::post('/checkout/confirm', [CheckoutController::class, 'confirm'])
        ->middleware('verified')
        ->name('checkout.confirm');
    Route::get('/dashboard/orders', [OrderController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard.orders');
    Route::get('/dashboard/orders/{orderId}/items/{productId}/return', [ReturnController::class, 'create'])
        ->middleware('verified')
        ->name('returns.create');
    Route::post('/dashboard/orders/{orderId}/items/{productId}/return', [ReturnController::class, 'store'])
        ->middleware('verified')
        ->name('returns.store');
    Route::get('/dashboard/returns/{returnId}', [ReturnController::class, 'show'])
        ->middleware('verified')
        ->name('returns.show');
    Route::delete('/dashboard/returns/{returnId}', [ReturnController::class, 'destroy'])
        ->middleware('verified')
        ->name('returns.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
