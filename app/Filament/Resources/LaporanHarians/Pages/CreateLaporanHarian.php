<?php

namespace App\Filament\Resources\LaporanHarians\Pages;

use App\Filament\Resources\LaporanHarians\LaporanHarianResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLaporanHarian extends CreateRecord
{
    protected static string $resource = LaporanHarianResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
