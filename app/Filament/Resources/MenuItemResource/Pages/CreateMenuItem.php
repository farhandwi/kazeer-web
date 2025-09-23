<?php

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Resources\MenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateMenuItem extends CreateRecord
{
    protected static string $resource = MenuItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure slug is unique within restaurant
        $baseSlug = $data['slug'];
        $counter = 1;
        while (\App\Models\MenuItem::where('restaurant_id', $data['restaurant_id'])
                ->where('slug', $data['slug'])
                ->exists()) {
            $data['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Convert allergens array to JSON if needed
        if (isset($data['allergens']) && is_array($data['allergens'])) {
            $data['allergens'] = $data['allergens'];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // If menu item has options, make sure has_options is set to true
        $record = $this->getRecord();
        if ($record->menuOptionCategories()->exists() && !$record->has_options) {
            $record->update(['has_options' => true]);
        }
    }
}