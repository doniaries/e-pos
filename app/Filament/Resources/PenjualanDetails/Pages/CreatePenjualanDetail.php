<?php

namespace App\Filament\Resources\PenjualanDetails\Pages;

use App\Filament\Resources\PenjualanDetails\PenjualanDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePenjualanDetail extends CreateRecord
{
    protected static string $resource = PenjualanDetailResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
