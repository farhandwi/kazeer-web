<?php

// app/Filament/Widgets/OrdersChart.php
namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Orders Overview';
    
    protected static ?int $sort = 2;
    
    public ?string $filter = '7days';
    
    protected function getData(): array
    {
        $days = match ($this->filter) {
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            default => 7,
        };
        
        $dates = collect();
        $orderCounts = collect();
        $revenues = collect();
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dates->push($date->format('M d'));
            
            $ordersCount = Order::whereDate('created_at', $date)->count();
            $revenue = Order::whereDate('created_at', $date)
                ->whereNotIn('status', ['cancelled'])
                ->sum('total_amount');
                
            $orderCounts->push($ordersCount);
            $revenues->push($revenue / 1000); // Convert to thousands for better readability
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $orderCounts->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Revenue (K)',
                    'data' => $revenues->toArray(),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $dates->toArray(),
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getFilters(): ?array
    {
        return [
            '7days' => 'Last 7 days',
            '30days' => 'Last 30 days',
            '90days' => 'Last 90 days',
        ];
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Date',
                    ],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Orders',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue (K)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}