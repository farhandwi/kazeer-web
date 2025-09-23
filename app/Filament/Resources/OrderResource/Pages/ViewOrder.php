<?php

// App/Filament/Resources/OrderResource/Pages/ViewOrder.php
namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('print_receipt')
                ->label('Print Receipt')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (): string => route('orders.print', $this->getRecord()))
                ->openUrlInNewTab(),
            Actions\Action::make('send_notification')
                ->label('Send Notification')
                ->icon('heroicon-o-bell')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\Textarea::make('message')
                        ->label('Notification Message')
                        ->required()
                        ->default(fn () => 'Your order #' . $this->getRecord()->order_number . ' status has been updated to: ' . ucfirst($this->getRecord()->status)),
                    \Filament\Forms\Components\Select::make('method')
                        ->label('Notification Method')
                        ->options([
                            'sms' => 'SMS',
                            'whatsapp' => 'WhatsApp',
                            'email' => 'Email',
                        ])
                        ->default('whatsapp')
                        ->required(),
                ])
                ->action(function (array $data) {
                    // Implementation for sending notification
                    // This would integrate with your notification service
                    \Filament\Notifications\Notification::make()
                        ->title('Notification Sent')
                        ->success()
                        ->send();
                }),
            Actions\ActionGroup::make([
                Actions\Action::make('mark_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(fn (): bool => $this->getRecord()->payment_status !== 'paid')
                    ->action(fn () => $this->getRecord()->update(['payment_status' => 'paid']))
                    ->requiresConfirmation(),
                Actions\Action::make('mark_preparing')
                    ->label('Mark as Preparing')
                    ->icon('heroicon-o-fire')
                    ->color('info')
                    ->visible(fn (): bool => !in_array($this->getRecord()->status, ['preparing', 'ready', 'served', 'completed']))
                    ->action(fn () => $this->getRecord()->update([
                        'status' => 'preparing',
                        'confirmed_at' => $this->getRecord()->confirmed_at ?? now(),
                    ]))
                    ->requiresConfirmation(),
                Actions\Action::make('mark_ready')
                    ->label('Mark as Ready')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (): bool => !in_array($this->getRecord()->status, ['ready', 'served', 'completed']))
                    ->action(fn () => $this->getRecord()->update([
                        'status' => 'ready',
                        'ready_at' => now(),
                        'confirmed_at' => $this->getRecord()->confirmed_at ?? now()->subMinutes(10),
                    ]))
                    ->requiresConfirmation(),
                Actions\Action::make('mark_served')
                    ->label('Mark as Served')
                    ->icon('heroicon-o-hand-raised')
                    ->color('success')
                    ->visible(fn (): bool => !in_array($this->getRecord()->status, ['served', 'completed']))
                    ->action(fn () => $this->getRecord()->update([
                        'status' => 'served',
                        'served_at' => now(),
                        'ready_at' => $this->getRecord()->ready_at ?? now()->subMinutes(2),
                    ]))
                    ->requiresConfirmation(),
            ])
                ->label('Quick Actions')
                ->icon('heroicon-o-bolt')
                ->button(),
        ];
    }
}