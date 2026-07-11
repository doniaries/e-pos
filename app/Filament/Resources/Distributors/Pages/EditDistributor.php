<?php

namespace App\Filament\Resources\Distributors\Pages;

use App\Filament\Resources\Distributors\DistributorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDistributor extends EditRecord
{
    protected static string $resource = DistributorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
