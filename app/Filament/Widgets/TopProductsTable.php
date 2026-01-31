<?php

namespace App\Filament\Widgets;

use App\Models\Produk;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopProductsTable extends BaseWidget
{
    public static function canView(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    protected static ?string $heading = '10 Barang Paling Laku';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Produk::query()
                    ->select('produks.id', 'produks.nama', 'produks.stok', 'produks.harga_jual')
                    ->join('penjualan_details', 'produks.id', '=', 'penjualan_details.produk_id')
                    ->selectRaw('SUM(penjualan_details.jumlah) as total_terjual')
                    ->groupBy('produks.id', 'produks.nama', 'produks.stok', 'produks.harga_jual')
                    ->orderByDesc('total_terjual')
                    ->limit(10)
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Produk')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('total_terjual')
                    ->label('Total Terjual')
                    ->suffix(' unit')
                    ->alignCenter()
                    ->color('success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('stok')
                    ->label('Sisa Stok')
                    ->alignCenter()
                    ->badge()
                    ->color(fn($state) => $state < 10 ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format((float) $state, 0, ',', '.') . ',-')
                    ->alignRight(),
            ]);
    }
}
