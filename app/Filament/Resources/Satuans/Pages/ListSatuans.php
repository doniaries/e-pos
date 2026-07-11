<?php

namespace App\Filament\Resources\Satuans\Pages;

use App\Filament\Resources\Satuans\SatuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSatuans extends ListRecords
{
    protected static string $resource = SatuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
