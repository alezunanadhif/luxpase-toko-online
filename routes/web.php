<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductsGalleryController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [FrontendController::class, 'index'])->name('index');
Route::get('/details/{slug}', [FrontendController::class, 'details'])->name('details');
Route::get('/cart', [FrontendController::class, 'cart'])->name('cart');
Route::get('/checkout/success', [FrontendController::class, 'success'])->name('checkout-success');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'IsAdmin'])->name('dashboard.')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    Route::resource('product', ProductController::class);
    Route::resource('product.gallery', ProductsGalleryController::class)->shallow()->only([
        'index', 'create', 'store', 'destroy'
    ]);
    Route::resource('transaction', TransactionsController::class);
    Route::resource('user', UserController::class);
});
