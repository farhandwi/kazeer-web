<?php

namespace App\Filament\Resources\TableResource\Pages;

use App\Filament\Resources\TableResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class EditTable extends EditRecord
{
    protected static string $resource = TableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),

            Action::make('regenerate_qr')
                ->label('Regenerate QR Code')
                ->icon('heroicon-o-qr-code')
                ->color('info')
                ->action(function () {
                    try {
                        $this->record->regenerateQRCode();
                        
                        Notification::make()
                            ->title('QR Code Regenerated')
                            ->body('QR code has been regenerated successfully.')
                            ->success()
                            ->send();

                        // Refresh the form to show new QR code
                        $this->fillForm();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Failed to regenerate QR code: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation(),

            Action::make('download_qr')
                ->label('Download QR Code')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    if (!$this->record->qr_code_path) {
                        Notification::make()
                            ->title('No QR Code')
                            ->body('Please generate QR code first.')
                            ->warning()
                            ->send();
                        return;
                    }

                    $filePath = storage_path('app/public/' . $this->record->qr_code_path);
                    if (file_exists($filePath)) {
                        return response()->download($filePath, "table_{$this->record->table_number}_qr_code.png");
                    }

                    Notification::make()
                        ->title('File Not Found')
                        ->body('QR code file not found.')
                        ->danger()
                        ->send();
                })
                ->visible(fn () => $this->record->qr_code_path),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Override form untuk edit page dengan QR preview
    protected function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Table Information')
                ->schema([
                    \Filament\Forms\Components\Select::make('restaurant_id')
                        ->label('Restaurant')
                        ->options(\App\Models\Restaurant::all()->pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload(),

                    \Filament\Forms\Components\TextInput::make('table_number')
                        ->label('Table Number')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(999),

                    \Filament\Forms\Components\TextInput::make('capacity')
                        ->label('Capacity')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(20)
                        ->suffix('people'),

                    \Filament\Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'available' => 'Available',
                            'occupied' => 'Occupied',
                            'reserved' => 'Reserved',
                            'maintenance' => 'Maintenance',
                            'inactive' => 'Inactive',
                        ])
                        ->default('available')
                        ->required(),

                    \Filament\Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->maxLength(500)
                        ->rows(3),
                ])
                ->columns(2),

            \Filament\Forms\Components\Section::make('QR Code Information')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('table_code')
                        ->label('Table Code')
                        ->disabled()
                        ->default(fn ($record) => $record?->table_code)
                        ->helperText('Auto-generated unique code for this table'),

                    \Filament\Forms\Components\TextInput::make('order_url_display')
                        ->label('Order URL')
                        ->disabled()
                        ->default(fn ($record) => $record?->getOrderUrl())
                        ->helperText('URL that customers scan to place orders'),

                    \Filament\Forms\Components\Placeholder::make('qr_preview')
                        ->label('QR Code')
                        ->content(function ($record) {
                            if (!$record || !$record->qr_code_url) {
                                return 'QR code will be generated automatically.';
                            }
                            
                            return new \Illuminate\Support\HtmlString('
                                <div class="text-center">
                                    <img src="' . $record->qr_code_url . '" 
                                         alt="QR Code" 
                                         class="mx-auto w-32 h-32 border border-gray-300 rounded">
                                    <p class="mt-2 text-sm text-gray-600">Table ' . $record->table_number . ' QR Code</p>
                                    <a href="' . $record->qr_code_url . '" 
                                       download="table_' . $record->table_number . '_qr.png"
                                       class="mt-2 inline-block px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600">
                                       Download QR Code
                                    </a>
                                </div>
                            ');
                        }),
                ])
                ->columns(1),
        ];
    }
}