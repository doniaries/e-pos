<?php

namespace App\Filament\Resources\Distributors\Pages;

use App\Filament\Resources\Distributors\DistributorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDistributors extends ListRecords
{
    protected static string $resource = DistributorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
