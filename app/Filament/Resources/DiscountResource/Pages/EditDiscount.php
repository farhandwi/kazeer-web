<?php

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Filament\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditDiscount extends EditRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('heroicon-o-eye'),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->requiresConfirmation(),
            Actions\Action::make('duplicate')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(function () {
                    $originalRecord = $this->record;
                    $newRecord = $originalRecord->replicate();
                    $newRecord->name = $originalRecord->name . ' (Copy)';
                    $newRecord->code = $originalRecord->code ? $originalRecord->code . '-copy' : null;
                    $newRecord->used_count = 0;
                    $newRecord->is_active = false; // Start as inactive
                    $newRecord->save();
                    
                    Notification::make()
                        ->success()
                        ->title('Discount duplicated')
                        ->body('A copy of this discount has been created.')
                        ->send();
                        
                    return redirect(static::getResource()::getUrl('edit', ['record' => $newRecord]));
                })
                ->requiresConfirmation()
                ->modalHeading('Duplicate Discount')
                ->modalDescription('This will create a copy of this discount. The copy will be inactive by default.'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Discount updated')
            ->body('The discount has been updated successfully.')
            ->duration(5000);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convert empty arrays to null
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