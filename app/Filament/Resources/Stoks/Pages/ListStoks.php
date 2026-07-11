<?php

namespace App\Filament\Resources\Stoks\Pages;

use App\Filament\Resources\Stoks\StokResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStoks extends ListRecords
{
    protected static string $resource = StokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
