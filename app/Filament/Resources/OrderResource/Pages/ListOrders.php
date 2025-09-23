<?php

// App/Filament/Resources/OrderResource/Pages/ListOrders.php
namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(fn () => $this->getModel()::count()),

            'pending' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => $this->getModel()::where('status', 'pending')->count())
                ->badgeColor('gray'),

            'confirmed' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'confirmed'))
                ->badge(fn () => $this->getModel()::where('status', 'confirmed')->count())
                ->badgeColor('warning'),

            'preparing' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'preparing'))
                ->badge(fn () => $this->getModel()::where('status', 'preparing')->count())
                ->badgeColor('info'),

            'ready' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'ready'))
                ->badge(fn () => $this->getModel()::where('status', 'ready')->count())
                ->badgeColor('success'),

            'completed' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => $this->getModel()::where('status', 'completed')->count())
                ->badgeColor('success'),
        ];
    }
}