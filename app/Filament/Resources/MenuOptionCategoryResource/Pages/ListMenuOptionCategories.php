<?php

namespace App\Filament\Resources\MenuOptionCategoryResource\Pages;

use App\Filament\Resources\MenuOptionCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMenuOptionCategories extends ListRecords
{
    protected static string $resource = MenuOptionCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Option Category'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(fn () => $this->getModel()::count()),

            'single_choice' => Tab::make('Single Choice')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'single'))
                ->badge(fn () => $this->getModel()::where('type', 'single')->count())
                ->badgeColor('primary'),

            'multiple_choice' => Tab::make('Multiple Choice')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'multiple'))
                ->badge(fn () => $this->getModel()::where('type', 'multiple')->count())
                ->badgeColor('success'),

            'required' => Tab::make('Required')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_required', true))
                ->badge(fn () => $this->getModel()::where('is_required', true)->count())
                ->badgeColor('warning'),

            'unused' => Tab::make('Unused')
                ->modifyQueryUsing(fn (Builder $query) => $query->doesntHave('menuItems'))
                ->badge(fn () => $this->getModel()::doesntHave('menuItems')->count())
                ->badgeColor('gray'),
        ];
    }
}