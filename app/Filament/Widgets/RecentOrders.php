<?php


// app/Filament/Widgets/RecentOrders.php
namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrders extends BaseWidget
{
    protected static ?string $heading = 'Recent Orders';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['restaurant', 'table', 'orderItems'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('table.table_number')
                    ->label('Table'),
                    
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'confirmed',
                        'info' => 'preparing',
                        'success' => ['ready', 'served', 'completed'],
                        'danger' => 'cancelled',
                    ]),
                    
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('IDR'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.view', $record))
                    ->icon('heroicon-o-eye')
                    ->color('info'),
            ]);
    }
}