<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $setting = $this->record;

        \App\Models\StoreStatus::updateOrCreate(
            ['tanggal' => now()->toDateString()],
            [
                'is_tutup' => $setting->is_toko_tutup,
                'catatan' => $setting->is_toko_tutup ? ($setting->pesan_tutup ?? 'Toko Tutup') : 'Toko Buka',
            ]
        );
    }
}
