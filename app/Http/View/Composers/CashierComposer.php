<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class CashierComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Pastikan user memiliki restaurant_id
            if ($user->restaurant_id) {
                $pendingOrdersCount = Order::where('restaurant_id', $user->restaurant_id)
                    ->where('status', 'pending')
                    ->count();
                
                $view->with('pendingOrdersCount', $pendingOrdersCount);
            } else {
                $view->with('pendingOrdersCount', 0);
            }
        } else {
            $view->with('pendingOrdersCount', 0);
        }
    }
}