<?php

// app/Filament/Widgets/StatsOverview.php
namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Table;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = Order::whereDate('created_at', today())
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');
        
        $thisMonthOrders = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $activeOrders = Order::whereNotIn('status', ['completed', 'cancelled'])->count();
        
        return [
            Stat::make('Today\'s Orders', $todayOrders)
                ->description('Orders placed today')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),
                
            Stat::make('Today\'s Revenue', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description('Revenue generated today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('This Month\'s Orders', $thisMonthOrders)
                ->description('Total orders this month')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
                
            Stat::make('Active Orders', $activeOrders)
                ->description('Orders in progress')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}

