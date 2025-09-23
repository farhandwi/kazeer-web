<?php


namespace App\Filament\Resources\TableResource\Pages;

use App\Filament\Resources\TableResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateTable extends CreateRecord
{
    protected static string $resource = TableResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Table created')
            ->body('The table has been created successfully with QR code generated.');
    }

    // Override form untuk create page tanpa QR section
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
                    \Filament\Forms\Components\Placeholder::make('info')
                        ->content('Table code and QR code will be automatically generated after creating this table.')
                        ->extraAttributes(['class' => 'text-gray-600']),
                ])
                ->columns(1),
        ];
    }
}