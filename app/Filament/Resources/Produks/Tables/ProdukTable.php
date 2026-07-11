<?php

namespace App\Filament\Resources\Produks\Tables;
use Illuminate\Database\Eloquent\Builder;

use Filament\Tables;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Tables\Actions;
use Filament\Tables\Filters;

class ProdukTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['kategori_produk']))
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Produk')
                    ->copyable()
                    ->searchable(['nama', 'kode_produk', 'kode_tambahan'])
                    ->description(fn(Produk $record) => $record->kode_produk . ($record->kode_tambahan ? ' | ' . $record->kode_tambahan : ''))
                    ->wrap(),
                Tables\Columns\TextColumn::make('kategori_produk.nama')
                    ->label('Kategori / Satuan')
                    ->description(fn(Produk $record) => 'Satuan: ' . $record->satuan->nama)
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_beli')
                    ->label('Harga Beli')
                    ->alignRight()
                    ->color('info')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format(self::formatCurrency($state), 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_jual')
                    ->label('Harga (Jual/Grosir)')
                    ->alignRight()
                    ->color('success')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->description(fn(Produk $record) => 'Grosir: Rp ' . number_format($record->harga_grosir, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->badge()
                    ->color(fn($state) => $state > 4 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                    ->description(fn($state) => $state > 4 ? 'Tersedia' : ($state > 0 ? 'Stok Rendah' : 'Habis'))
                    ->tooltip(fn($state) => $state > 4 ? 'Stok produk di gudang masih mencukupi' : ($state > 0 ? 'Peringatan: Stok menipis, segera re-stok produk ini!' : 'Bahaya: Stok kosong! Unit tidak bisa dijual.'))
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_produk_id')
                    ->label('Kategori')
                    ->relationship('kategori_produk', 'nama')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn() => \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\ProductTemplateExport,
                        'template_produk.xlsx'
                    )),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::SixExtraLarge)
                    ->modalAutofocus(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\Action::make('cetak_barcode')
                    ->label('Cetak Barcode')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn(Produk $record) => route('barcode.label103', ['products' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('cetak_barcode_bulk')
                        ->label('Cetak Barcode Label 103')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function ($records) {
                            $products = $records;

                            // If only 1 product selected, duplicate to fill 12 labels (Full Sheet)
                            if ($products->count() === 1) {
                                $singleProduct = $products->first();
                                $products = collect(array_fill(0, 12, $singleProduct));
                            }

                            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                            $barcodes = [];

                            foreach ($products as $product) {
                                $barcodeData = $product->kode_produk;
                                $barcodeImage = base64_encode($generator->getBarcode(
                                    $barcodeData,
                                    $generator::TYPE_CODE_128,
                                    3,
                                    50
                                ));

                                $barcodes[] = [
                                    'image' => $barcodeImage,
                                    'code' => $barcodeData,
                                    'name' => $product->nama,
                                    'price' => 'Rp ' . number_format($product->harga_jual, 0, ',', '.'),
                                ];
                            }

                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('barcode.label-103', [
                                'barcodes' => collect($barcodes)
                            ]);

                            $pdf->setPaper([0, 0, 595.28, 456.38], 'portrait');

                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, 'barcodes-' . now()->format('YmdHis') . '.pdf');
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
