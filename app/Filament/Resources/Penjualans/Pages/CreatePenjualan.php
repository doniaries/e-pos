<?php

namespace App\Filament\Resources\Penjualans\Pages;

use App\Filament\Resources\Penjualans\PenjualanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePenjualan extends CreateRecord
{
    protected static string $resource = PenjualanResource::class;



    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function beforeCreate(): void
    {
        // Generate nomor invoice
        $this->data['nomor'] = \App\Models\Penjualan::generateNomor();
        // Set user_id (kasir)
        $this->data['user_id'] = auth()->id();
    }

    protected function afterCreate(): void
    {
        // Proses pembayaran
        $this->record->pembayarans()->create([
            'metode' => $this->data['metode_pembayaran'],
            'jumlah' => $this->data['bayar'],
        ]);

        // Update stok
        foreach ($this->record->details as $detail) {
            $detail->updateStok();
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
