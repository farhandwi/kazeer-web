<?php

// App/Filament/Resources/OrderResource/Pages/EditOrder.php
namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('print_receipt')
                ->label('Print Receipt')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (): string => route('orders.print', $this->getRecord()))
                ->openUrlInNewTab(),
            Actions\Action::make('duplicate_order')
                ->label('Duplicate Order')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->action(function () {
                    $original = $this->getRecord();
                    $duplicate = $original->replicate();
                    $duplicate->order_number = 'ORD-' . strtoupper(uniqid());
                    $duplicate->status = 'pending';
                    $duplicate->payment_status = 'pending';
                    $duplicate->confirmed_at = null;
                    $duplicate->ready_at = null;
                    $duplicate->served_at = null;
                    $duplicate->completed_at = null;
                    $duplicate->save();

                    // Duplicate order items
                    foreach ($original->orderItems as $item) {
                        $duplicateItem = $item->replicate();
                        $duplicateItem->order_id = $duplicate->id;
                        $duplicateItem->status = 'pending';
                        $duplicateItem->started_at = null;
                        $duplicateItem->ready_at = null;
                        $duplicateItem->served_at = null;
                        $duplicateItem->save();
                    }

                    return redirect()->route('filament.admin.resources.orders.edit', $duplicate);
                })
                ->requiresConfirmation()
                ->modalHeading('Duplicate Order')
                ->modalDescription('This will create a new order with the same items and details.'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $original = $this->getRecord();

        // Auto-update timestamps based on status changes
        if ($data['status'] !== $original->status) {
            switch ($data['status']) {
                case 'confirmed':
                    if (empty($data['confirmed_at'])) {
                        $data['confirmed_at'] = now();
                    }
                    break;
                case 'ready':
                    if (empty($data['ready_at'])) {
                        $data['ready_at'] = now();
                    }
                    if (empty($data['confirmed_at'])) {
                        $data['confirmed_at'] = now()->subMinutes(10);
                    }
                    break;
                case 'served':
                    if (empty($data['served_at'])) {
                        $data['served_at'] = now();
                    }
                    if (empty($data['ready_at'])) {
                        $data['ready_at'] = now()->subMinutes(5);
                    }
                    break;
                case 'completed':
                    if (empty($data['completed_at'])) {
                        $data['completed_at'] = now();
                    }
                    if (empty($data['served_at'])) {
                        $data['served_at'] = now()->subMinutes(2);
                    }
                    break;
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
