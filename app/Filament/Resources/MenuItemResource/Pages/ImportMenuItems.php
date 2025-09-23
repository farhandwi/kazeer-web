<?php

namespace App\Filament\Resources\MenuItemResource\Pages;

use Filament\Resources\Pages\Page;
use App\Filament\Resources\MenuItemResource;

class ImportMenuItems extends Page
{
    protected static string $resource = MenuItemResource::class;

    protected static string $view = 'filament.resources.menu-item-resource.pages.import-menu-items';

    protected static ?string $title = 'Import Menu Items';
}
