<?php

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Filament\Resources\DiscountResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateDiscount extends CreateRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Discount created')
            ->body('The discount has been created successfully.')
            ->duration(5000);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure used_count starts at 0
        $data['used_count'] = 0;

        // Convert array fields to JSON if they're arrays
        if (isset($data['applicable_restaurants']) && is_array($data['applicable_restaurants'])) {
            $data['applicable_restaurants'] = empty($data['applicable_restaurants']) ? null : $data['applicable_restaurants'];
        }
        
        if (isset($data['applicable_categories']) && is_array($data['applicable_categories'])) {
            $data['applicable_categories'] = empty($data['applicable_categories']) ? null : $data['applicable_categories'];
        }
        
        if (isset($data['applicable_menu_items']) && is_array($data['applicable_menu_items'])) {
            $data['applicable_menu_items'] = empty($data['applicable_menu_items']) ? null : $data['applicable_menu_items'];
        }

        return $data;
    }
}