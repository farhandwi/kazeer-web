<?php


// app/Filament/Widgets/TableStatusOverview.php
namespace App\Filament\Widgets;

use App\Models\Table;
use Filament\Widgets\ChartWidget;

class TableStatusOverview extends ChartWidget
{
    protected static ?string $heading = 'Table Status Distribution';
    
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $statusCounts = Table::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
            
        return [
            'datasets' => [
                [
                    'data' => array_values($statusCounts),
                    'backgroundColor' => [
                        '#10b981', // available - green
                        '#f59e0b', // reserved - yellow
                        '#ef4444', // occupied - red
                        '#6b7280', // maintenance - gray
                    ],
                ],
            ],
            'labels' => array_map('ucfirst', array_keys($statusCounts)),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}