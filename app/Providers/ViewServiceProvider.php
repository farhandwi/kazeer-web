<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\CashierComposer;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Bind CashierComposer ke semua view yang dimulai dengan 'cashier.'
        View::composer('cashier.*', CashierComposer::class);
        
        // Atau jika ingin lebih spesifik ke view tertentu:
        // View::composer(['cashier.index', 'cashier.cart', 'cashier.orders'], CashierComposer::class);
    }
}