<?php

namespace App\Filament\Resources\Satuans\Pages;

use App\Filament\Resources\Satuans\SatuanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSatuan extends CreateRecord
{
    protected static string $resource = SatuanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
