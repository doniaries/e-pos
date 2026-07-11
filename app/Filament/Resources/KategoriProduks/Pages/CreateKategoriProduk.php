<?php

namespace App\Filament\Resources\KategoriProduks\Pages;

use App\Filament\Resources\KategoriProduks\KategoriProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKategoriProduk extends CreateRecord
{
    protected static string $resource = KategoriProdukResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
