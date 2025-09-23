<?php

namespace App\Filament\Resources\KitchenStationResource\Pages;

use App\Filament\Resources\KitchenStationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKitchenStations extends ListRecords
{
    protected static string $resource = KitchenStationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
