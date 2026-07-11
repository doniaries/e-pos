<?php

namespace App\Filament\Resources\Produks\Pages;

use Filament\Forms;

use App\Filament\Resources\Produks\ProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduk extends EditRecord
{
    protected static string $resource = ProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('adjust_stok')
                ->label('Penyesuaian Stok')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Select::make('jenis_penyesuaian')
                        ->label('Jenis Penyesuaian')
                        ->options([
                            'tambah' => 'Tambah Stok (Masuk)',
                            'kurang' => 'Kurang Stok (Keluar)',
                            'atur_ulang' => 'Atur Ulang (Opname)',
                        ])
                        ->required()
                        ->live(),
                    \Filament\Forms\Components\TextInput::make('jumlah')
                        ->label(fn(\Filament\Forms\Get $get) => match ($get('jenis_penyesuaian')) {
                            'atur_ulang' => 'Stok Fisik (Nyata)',
                            default => 'Jumlah',
                        })
                        ->numeric()
                        ->required()
                        ->minValue(1),
                    \Filament\Forms\Components\Textarea::make('keterangan')
                        ->required()
                        ->placeholder('Contoh: Barang rusak, Bonus supplier, Selisih opname'),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    $jumlah = (int) $data['jumlah'];
                    $currentStok = $record->stok;
                    $delta = 0;
                    $jenis = 'adjustment';

                    switch ($data['jenis_penyesuaian']) {
                        case 'tambah':
                            $delta = $jumlah;
                            $jenis = 'pembelian';
                            break;
                        case 'kurang':
                            $delta = -$jumlah;
                            $jenis = 'penjualan';
                            break;
                        case 'atur_ulang':
                            $delta = $jumlah - $currentStok;
                            $jenis = $delta > 0 ? 'pembelian' : 'penjualan';
                            break;
                    }

                    if ($delta == 0) {
                        \Filament\Notifications\Notification::make()
                            ->title('Tidak ada perubahan stok')
                            ->warning()
                            ->send();
                        return;
                    }

                    \App\Models\Stok::create([
                        'produk_id' => $record->id,
                        'jenis' => $jenis,
                        'jumlah' => $delta,
                        'keterangan' => $data['keterangan'] . ' (Via Edit Produk)',
                        'user_id' => auth()->id(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Stok berhasil diperbarui')
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
