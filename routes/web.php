<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;

// Route untuk preview menu item (customer view)
Route::get('/menu/preview/{menuItem}', function (MenuItem $menuItem) {
    return view('menu.preview', [
        'menuItem' => $menuItem->load(['restaurant', 'category', 'optionCategories.options'])
    ]);
})->name('menu.preview');

// Route untuk single menu item (customer view)
Route::get('/restaurant/{restaurant}/menu/{item}', function (Restaurant $restaurant, $itemSlug) {
    $menuItem = $restaurant->menuItems()
        ->where('slug', $itemSlug)
        ->with(['category', 'optionCategories.options'])
        ->firstOrFail();
        
    return view('menu.item', [
        'restaurant' => $restaurant,
        'menuItem' => $menuItem
    ]);
})->name('menu.item');

// Route untuk analytics (jika diperlukan)
Route::get('/admin/menu-items/{menuItem}/analytics', function (MenuItem $menuItem) {
    return view('filament.pages.menu-item-analytics', [
        'menuItem' => $menuItem
    ]);
})->name('filament.admin.resources.menu-items.analytics');

Route::get('/', function () {
    return view('welcome');
});

// Login routes (hanya untuk guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

$panels = ['admin', 'kitchen', 'waiter', 'cashier'];

foreach ($panels as $panel) {
    Route::post("/{$panel}/logout", [LoginController::class, 'logout'])
    ->name("filament.{$panel}.auth.logout")
    ->middleware(['web', 'auth']); // 'web' biasanya otomatis, tapi tidak apa-apa menambahkan
}

// Route::post('/logout', [LoginController::class, 'logout'])->name('filament.admin.auth.logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        /** @var \App\Models\Staff $user */
        $user = Auth::user();

        return redirect($user->redirectPath());
    })->name('dashboard');
    
    // Fallback untuk role-based redirects
    Route::get('/admin-dashboard', function () {
        /** @var \App\Models\Staff $user */
        $user = Auth::user();
    
        if ($user?->isAdmin()) {
            return redirect('/admin');
        }
    
        return redirect($user->redirectPath());
    });
    
    Route::get('/kitchen-dashboard', function () {
        /** @var \App\Models\Staff $user */
        $user = Auth::user();
        if ($user?->isKitchen()) {
            return redirect('/kitchen');
        }
        return redirect($user?->redirectPath());
    });
    
    Route::get('/cashier-dashboard', function () {
        /** @var \App\Models\Staff $user */
        $user = Auth::user();
        if ($user?->isCashier()) {
            return redirect('/cashier');
        }
        return redirect($user?->redirectPath());
    });
    
    Route::get('/waiter-dashboard', function () {
        /** @var \App\Models\Staff $user */
        $user = Auth::user();
        if ($user?->isWaiter()) {
            return redirect('/waiter');
        }
        return redirect($user?->redirectPath());
    });
});

// Include cashier routes
require __DIR__.'/cashier.php';