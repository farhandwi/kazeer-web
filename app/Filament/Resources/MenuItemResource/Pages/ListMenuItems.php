<?php

// App/Filament/Resources/MenuItemResource/Pages/ListMenuItems.php
namespace App\Filament\Resources\MenuItemResource\Pages;
use App\Filament\Resources\MenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMenuItems extends ListRecords
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Import Menu Items')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->url(route('filament.admin.resources.menu-items.import'))
                ->visible(fn (): bool => request()->user()?->can('import_menu_items')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(fn () => $this->getModel()::count()),

            'available' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_available', true))
                ->badge(fn () => $this->getModel()::where('is_available', true)->count())
                ->badgeColor('success'),

            'unavailable' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_available', false))
                ->badge(fn () => $this->getModel()::where('is_available', false)->count())
                ->badgeColor('danger'),

            'featured' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true))
                ->badge(fn () => $this->getModel()::where('is_featured', true)->count())
                ->badgeColor('warning'),

            'with_options' => Tab::make()
                ->label('With Options')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('has_options', true))
                ->badge(fn () => $this->getModel()::where('has_options', true)->count())
                ->badgeColor('info'),

            'spicy' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('spice_level', ['hot', 'very_hot']))
                ->badge(fn () => $this->getModel()::whereIn('spice_level', ['hot', 'very_hot'])->count())
                ->badgeColor('danger'),
        ];
    }
}
