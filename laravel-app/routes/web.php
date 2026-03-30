<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
