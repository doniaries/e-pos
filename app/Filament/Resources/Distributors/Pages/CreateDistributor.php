<?php

namespace App\Filament\Resources\Distributors\Pages;

use App\Filament\Resources\Distributors\DistributorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDistributor extends CreateRecord
{
    protected static string $resource = DistributorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
