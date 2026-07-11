<?php
// app/Filament/Resources/PenjualanResource/Pages/ViewPenjualan.php

namespace App\Filament\Resources\Penjualans\Pages;

use App\Filament\Resources\Penjualans\PenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPenjualan extends ViewRecord
{
    protected static string $resource = PenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Cetak Struk')
                ->color('success')
                ->icon('heroicon-o-printer')
                ->url(fn() => route('pos.print-struk', ['id' => $this->record->id])),
        ];
    }
}
