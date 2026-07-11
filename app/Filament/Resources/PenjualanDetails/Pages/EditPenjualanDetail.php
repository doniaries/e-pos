<?php

namespace App\Filament\Resources\PenjualanDetails\Pages;

use App\Filament\Resources\PenjualanDetails\PenjualanDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenjualanDetail extends EditRecord
{
    protected static string $resource = PenjualanDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
