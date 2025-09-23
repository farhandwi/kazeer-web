<?php

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Filament\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDiscounts extends ListRecords
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->label('Create Discount'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Discounts')
                ->badge(fn () => $this->getModel()::count()),

            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->active())
                ->badge(fn () => $this->getModel()::active()->count())
                ->badgeColor('success'),

            'scheduled' => Tab::make('Scheduled')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('is_active', true)
                          ->where('starts_at', '>', now())
                )
                ->badge(fn () => $this->getModel()::where('is_active', true)
                    ->where('starts_at', '>', now())->count())
                ->badgeColor('warning'),

            'expired' => Tab::make('Expired')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('expires_at', '<', now())
                )
                ->badge(fn () => $this->getModel()::where('expires_at', '<', now())->count())
                ->badgeColor('danger'),

            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(fn () => $this->getModel()::where('is_active', false)->count())
                ->badgeColor('gray'),
        ];
    }
}