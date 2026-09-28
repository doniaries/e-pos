<?php

namespace App\Filament\Resources\AppInfoResource\Pages;

use App\Filament\Resources\AppInfoResource\AppInfoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppInfos extends ListRecords
{
    protected static string $resource = AppInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
