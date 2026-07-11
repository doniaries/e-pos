<?php

namespace App\Filament\Resources\Aplikasis\Pages;

use App\Filament\Resources\Aplikasis\AplikasiResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAplikasi extends CreateRecord
{
    protected static string $resource = AplikasiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
