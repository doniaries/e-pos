<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Produk;
use Filament\Forms\Form as Schema;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProdukResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProdukResource\RelationManagers;

use Filament\Support\Enums\MaxWidth;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';

    public static function shouldRegisterNavigation(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\ProdukResource\Schemas\ProdukSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProduks::route('/'),
            // 'create' => Pages\CreateProduk::route('/create'),
            // 'edit' => Pages\EditProduk::route('/{record}/edit'),
        ];
    }


    // In ProdukResource.php - Optimize search
    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_produk', 'kode_tambahan', 'nama'];
    }

    //Optimize Query di Resource
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select(['id', 'kode_produk', 'kode_tambahan', 'nama', 'kategori_produk_id', 'satuan_id', 'harga_beli', 'harga_jual', 'harga_grosir', 'stok', 'gambar_produk'])
            ->with(['kategori_produk:id,nama', 'satuan:id,nama']);
    }

    private static function formatCurrency($number): int
    {
        if (empty($number)) return 0;
        // Handle string format currency
        if (is_string($number)) {
            return (int) str_replace(['.', ','], ['', '.'], $number);
        }
        return (int) $number;
    }
}
