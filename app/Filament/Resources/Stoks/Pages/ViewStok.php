<?php

namespace App\Filament\Resources\Stoks\Pages;

use App\Filament\Resources\Stoks\StokResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStok extends ViewRecord
{
    protected static string $resource = StokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
