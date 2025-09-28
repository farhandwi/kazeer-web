<?php

use App\Http\Middleware\CheckCashierAccess;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CashierController;

/*
|--------------------------------------------------------------------------
| Cashier Routes
|--------------------------------------------------------------------------
*/

// Route::prefix('cashier')->name('cashier.')->middleware(['auth', CheckCashierAccess::class])->group(function () {
//     // Dashboard
//     Route::get('/', [CashierController::class, 'dashboard'])->name('dashboard');
//     Route::get('/dashboard', [CashierController::class, 'dashboard'])->name('dashboard');
    
//     // Orders
//     Route::get('/orders', [CashierController::class, 'orders'])->name('orders');
//     Route::get('/order-menu', [CashierController::class, 'showOrderMenu'])->name('order-menu');
//     Route::post('/orders', [CashierController::class, 'createOrder'])->name('orders.create');
//     Route::patch('/orders/{order}/status', [CashierController::class, 'updateOrderStatus'])->name('orders.update-status');
//     Route::post('/orders/{order}/payment', [CashierController::class, 'processPayment'])->name('orders.payment');
    
//     // Menu
//     Route::get('/menu-items/{menuItem}/options', [CashierController::class, 'getMenuItemOptions'])->name('menu-items.options');
    
//     // Additional features
//     Route::get('/customers', [CashierController::class, 'customers'])->name('customers');
//     Route::get('/reports', [CashierController::class, 'reports'])->name('reports');
// });

Route::prefix('cashier')->name('cashier.')->middleware(['auth', CheckCashierAccess::class])->group(function () {
    Route::get('/', [CashierController::class, 'index'])->name('index');
    Route::get('/cart', [CashierController::class, 'cart'])->name('cart');
    Route::get('/menu-items', [CashierController::class, 'getMenuItems'])->name('menu-items');
    Route::get('/menu-items/{id}', [CashierController::class, 'getMenuItem'])->name('menu-items.show');
    Route::post('/orders', [CashierController::class, 'createOrder'])->name('orders.create');
    Route::get('/orders', [CashierController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [CashierController::class, 'showOrder'])->name('orders.show');
    Route::patch('/orders/{id}/status', [CashierController::class, 'updateOrderStatus'])->name('orders.update-status');
    Route::get('/settings', [CashierController::class, 'settings'])->name('settings');
    Route::post('/validate-coupon', [CashierController::class, 'validateCoupon'])->name('cashier.validate-coupon');
    Route::post('/validate-discount', [CashierController::class, 'validateDiscount'])->name('cashier.validate-discount');
});