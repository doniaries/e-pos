<?php

namespace App\Filament\Resources\Aplikasis\Pages;

use App\Filament\Resources\Aplikasis\AplikasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAplikasis extends ListRecords
{
    protected static string $resource = AplikasiResource::class;

    protected function getHeaderActions(): array
    {
        // Only show create button if no record exists
        if (\App\Models\Aplikasi::count() > 0) {
            return [];
        }

        return [
            Actions\CreateAction::make(),
        ];
    }
}
