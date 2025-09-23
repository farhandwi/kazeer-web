<?php

namespace App\Filament\Resources\TableResource\Pages;

use App\Filament\Resources\TableResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewTable extends ViewRecord
{
    protected static string $resource = TableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Table Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('restaurant.name')
                            ->label('Restaurant'),
                        Infolists\Components\TextEntry::make('table_number')
                            ->label('Table Number'),
                        Infolists\Components\TextEntry::make('capacity')
                            ->label('Capacity')
                            ->suffix(' people'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'available' => 'success',
                                'occupied' => 'warning',
                                'reserved' => 'info',
                                'maintenance' => 'danger',
                                'inactive' => 'secondary',
                            }),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description'),
                    ])->columns(2),

                Infolists\Components\Section::make('QR Code & Access')
                    ->schema([
                        Infolists\Components\TextEntry::make('table_code')
                            ->label('Table Code')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('order_url')
                            ->label('Order URL')
                            ->state(fn () => $this->record->getOrderUrl())
                            ->copyable(),
                        Infolists\Components\ImageEntry::make('qr_code_url')
                            ->label('QR Code')
                            ->size(200),
                    ])->columns(1),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])->columns(2),
            ]);
    }
}