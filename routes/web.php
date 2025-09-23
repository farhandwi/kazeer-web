<?php

use Illuminate\Support\Facades\Route;
use App\Models\MenuItem;
use App\Models\Restaurant;

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
