<?php

// App/Filament/Resources/OrderResource/Pages/CreateOrder.php
namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate order number if not provided
        if (empty($data['order_number'])) {
            $data['order_number'] = 'ORD-' . strtoupper(uniqid());
        }

        // Set timestamps based on status
        if ($data['status'] === 'confirmed' && empty($data['confirmed_at'])) {
            $data['confirmed_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send notification or trigger events here if needed
        // Example: OrderCreated event
        // event(new OrderCreated($this->getRecord()));
    }
}