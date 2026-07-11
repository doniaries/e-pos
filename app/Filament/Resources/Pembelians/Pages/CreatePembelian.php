<?php

namespace App\Filament\Resources\Pembelians\Pages;

use App\Filament\Resources\Pembelians\PembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePembelian extends CreateRecord
{
    protected static string $resource = PembelianResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
