<?php

// app/Filament/Widgets/PopularMenuItems.php
namespace App\Filament\Widgets;

use App\Models\MenuItem;
use App\Models\OrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PopularMenuItems extends BaseWidget
{
    protected static ?string $heading = 'Popular Menu Items';
    
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MenuItem::query()
                    ->withCount(['orderItems' => function ($query) {
                        $query->whereHas('order', function ($orderQuery) {
                            $orderQuery->whereNotIn('status', ['cancelled'])
                                ->whereDate('created_at', '>=', now()->subDays(30));
                        });
                    }])
                    ->orderByDesc('order_items_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular()
                    ->size(40),
                    
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR'),
                    
                Tables\Columns\TextColumn::make('order_items_count')
                    ->label('Orders (30 days)')
                    ->badge()
                    ->color('success'),
            ])
            ->paginated(false);
    }
}