<?php

namespace App\Filament\Resources\AppInfoResource\Pages;

use App\Filament\Resources\AppInfoResource\AppInfoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppInfo extends EditRecord
{
    protected static string $resource = AppInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
