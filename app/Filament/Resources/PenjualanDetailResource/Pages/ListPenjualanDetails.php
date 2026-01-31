<?php

namespace App\Filament\Resources\PenjualanDetailResource\Pages;

use App\Filament\Resources\PenjualanDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenjualanDetails extends ListRecords
{
    protected static string $resource = PenjualanDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}