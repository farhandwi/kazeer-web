<?php

namespace App\Filament\Resources\TableResource\Pages;

use App\Filament\Resources\TableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ListTables extends ListRecords
{
    protected static string $resource = TableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Action::make('bulk_generate_qr')
                ->label('Generate All QR Codes')
                ->icon('heroicon-o-qr-code')
                ->color('info')
                ->action(function () {
                    $tables = $this->getResource()::getEloquentQuery()->get();
                    $count = 0;
                    
                    foreach ($tables as $table) {
                        try {
                            if (!$table->qr_code_path) {
                                $table->generateQRCode();
                                $count++;
                            }
                        } catch (\Exception $e) {
                            // Continue with other tables
                        }
                    }

                    Notification::make()
                        ->title('QR Codes Generated')
                        ->body("Generated {$count} new QR codes.")
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Generate QR Codes')
                ->modalDescription('This will generate QR codes for all tables that don\'t have one yet.')
                ->modalSubmitActionLabel('Yes, generate'),
        ];
    }
}