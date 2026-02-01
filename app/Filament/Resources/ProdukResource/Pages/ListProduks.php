<?php

namespace App\Filament\Resources\ProdukResource\Pages;

use App\Filament\Resources\ProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use Filament\Support\Enums\MaxWidth;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ListProduks extends ListRecords
{
    protected static string $resource = ProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->label('Import Produk')
                ->modalHeading('Import Data Produk')
                ->modalDescription('Unggah file Excel (.xlsx) atau CSV untuk mengimpor data produk ke database.')
                ->modalSubmitActionLabel('Impor')
                ->modalCancelActionLabel('Batal')
                ->color('info')
                ->icon('heroicon-o-arrow-up-tray')
                ->processCollectionUsing(function ($collection) {
                    foreach ($collection as $row) {
                        try {
                            // Find or create Kategori
                            $kategoriId = null;
                            if (!empty($row['kategori'])) {
                                $kategori = \App\Models\KategoriProduk::firstOrCreate(
                                    ['nama' => strtoupper($row['kategori'])]
                                );
                                $kategoriId = $kategori->id;
                            }

                            // Find or create Satuan
                            $satuanId = null;
                            if (!empty($row['satuan'])) {
                                $satuan = \App\Models\Satuan::firstOrCreate(
                                    ['nama' => strtoupper($row['satuan'])]
                                );
                                $satuanId = $satuan->id;
                            }

                            // Create or update Produk
                            \App\Models\Produk::updateOrCreate(
                                [
                                    'kode_produk' => strtoupper($row['kode_produk']),
                                    'kode_tambahan' => !empty($row['kode_tambahan']) ? strtoupper($row['kode_tambahan']) : null,
                                ],
                                [
                                    'nama' => strtoupper($row['nama']),
                                    'kategori_produk_id' => $kategoriId,
                                    'satuan_id' => $satuanId,
                                    'harga_beli' => (int) ($row['harga_beli'] ?? 0),
                                    'harga_jual' => (int) ($row['harga_jual'] ?? 0),
                                    'harga_grosir' => (int) ($row['harga_grosir'] ?? $row['harga_jual'] ?? 0),
                                    'stok' => (int) ($row['stok'] ?? 0),
                                    'stok_minimum' => 1,
                                ]
                            );
                        } catch (\Exception $e) {
                            Log::error('Import error for row: ' . json_encode($row) . ' - ' . $e->getMessage());
                            continue;
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Import Selesai')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make()
                ->modalWidth(MaxWidth::SixExtraLarge)
                ->modalAutofocus(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->icon('heroicon-m-list-bullet'),
            'stok_banyak' => Tab::make('Stok Banyak')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('stok', '>', \Illuminate\Support\Facades\DB::raw('stok_minimum')))
                ->icon('heroicon-m-check-circle')
                ->badge(fn() => \App\Models\Produk::where('stok', '>', \Illuminate\Support\Facades\DB::raw('stok_minimum'))->count())
                ->badgeColor('success'),
            'stok_menipis' => Tab::make('Stok Menipis')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('stok', '<=', \Illuminate\Support\Facades\DB::raw('stok_minimum'))->where('stok', '>', 0))
                ->icon('heroicon-m-exclamation-triangle')
                ->badge(fn() => \App\Models\Produk::where('stok', '<=', \Illuminate\Support\Facades\DB::raw('stok_minimum'))->where('stok', '>', 0)->count())
                ->badgeColor('warning'),
            'stok_habis' => Tab::make('Stok Habis')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('stok', '<=', 0))
                ->icon('heroicon-m-x-circle')
                ->badge(fn() => \App\Models\Produk::where('stok', '<=', 0)->count())
                ->badgeColor('danger'),
        ];
    }
}
