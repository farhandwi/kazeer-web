<?php

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Resources\MenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMenuItem extends EditRecord
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('duplicate')
                ->label('Duplicate Item')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->action(function () {
                    $original = $this->getRecord();
                    $duplicate = $original->replicate();
                    $duplicate->name = $original->name . ' (Copy)';
                    $duplicate->slug = $original->slug . '-copy';
                    $duplicate->is_featured = false;
                    
                    // Ensure unique slug
                    $baseSlug = $duplicate->slug;
                    $counter = 1;
                    while (\App\Models\MenuItem::where('restaurant_id', $duplicate->restaurant_id)
                            ->where('slug', $duplicate->slug)
                            ->exists()) {
                        $duplicate->slug = $baseSlug . '-' . $counter;
                        $counter++;
                    }
                    
                    $duplicate->save();
                    
                    // Copy option categories relationships
                    foreach ($original->menuOptionCategories as $optionCategory) {
                        $duplicate->menuOptionCategories()->attach($optionCategory->id, [
                            'is_required' => $optionCategory->pivot->is_required,
                            'sort_order' => $optionCategory->pivot->sort_order,
                        ]);
                    }
                    
                    return redirect()->route('filament.admin.resources.menu-items.edit', $duplicate);
                })
                ->requiresConfirmation()
                ->modalHeading('Duplicate Menu Item')
                ->modalDescription('This will create a copy of this menu item with all its options.'),
            Actions\ActionGroup::make([
                Actions\Action::make('toggle_availability')
                    ->label(fn () => $this->getRecord()->is_available ? 'Mark Unavailable' : 'Mark Available')
                    ->icon(fn () => $this->getRecord()->is_available ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn () => $this->getRecord()->is_available ? 'warning' : 'success')
                    ->action(fn () => $this->getRecord()->update(['is_available' => !$this->getRecord()->is_available]))
                    ->requiresConfirmation(),
                Actions\Action::make('toggle_featured')
                    ->label(fn () => $this->getRecord()->is_featured ? 'Remove from Featured' : 'Mark as Featured')
                    ->icon(fn () => $this->getRecord()->is_featured ? 'heroicon-o-star' : 'heroicon-o-star')
                    ->color(fn () => $this->getRecord()->is_featured ? 'warning' : 'success')
                    ->action(fn () => $this->getRecord()->update(['is_featured' => !$this->getRecord()->is_featured])),
                Actions\Action::make('view_orders')
                    ->label('View Orders')
                    ->icon('heroicon-o-shopping-bag')
                    ->color('gray')
                    ->url(fn (): string => 
                        route('filament.admin.resources.orders.index', [
                            'tableFilters' => [
                                'orderItems' => [
                                    'menu_item_id' => $this->getRecord()->id
                                ]
                            ]
                        ])
                    ),
            ])
                ->label('Actions')
                ->icon('heroicon-o-ellipsis-vertical')
                ->button(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update has_options based on whether there are option categories
        $record = $this->getRecord();
        $hasOptionCategories = $record->menuOptionCategories()->exists();
        
        if ($data['has_options'] && !$hasOptionCategories) {
            // If marked as has_options but no categories attached, keep it false
            $data['has_options'] = false;
        } elseif (!$data['has_options'] && $hasOptionCategories) {
            // If not marked but has categories, set to true
            $data['has_options'] = true;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}