<?php

namespace App\Filament\Resources\KitchenStationResource\Pages;

use App\Filament\Resources\KitchenStationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKitchenStation extends EditRecord
{
    protected static string $resource = KitchenStationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
