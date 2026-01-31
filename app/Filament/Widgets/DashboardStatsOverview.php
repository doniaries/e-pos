<?php

namespace App\Filament\Widgets;

use App\Models\Distributor;
use App\Models\Penjualan;
use App\Models\Produk;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Jumlah Barang', Produk::count())
                ->description('Total item produk terdaftar')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary')
                ->url(route('filament.admin.resources.produks.index')),

            Stat::make('Jumlah Distributor', Distributor::count())
                ->description('Total mitra distributor')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info')
                ->url(route('filament.admin.resources.distributors.index')),

            Stat::make('Transaksi Hari Ini', Penjualan::whereDate('created_at', today())->count())
                ->description('Total transaksi hari ini')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success')
                ->url(route('filament.admin.resources.penjualan.index')),

            Stat::make('Pendapatan Hari Ini', 'Rp ' . number_format(Penjualan::whereDate('created_at', today())->sum('total'), 0, ',', '.'))
                ->description('Total penjualan hari ini')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->url(route('pos')),

            Stat::make('Stok Habis', Produk::where('stok', '<=', 0)->count())
                ->description('Produk dengan stok 0')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(route('filament.admin.resources.produks.index')),

            Stat::make('Stok Akan Habis', Produk::where('stok', '>', 0)->where('stok', '<', 5)->count())
                ->description('Produk dengan stok kurang dari 5')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->url(route('filament.admin.resources.produks.index')),


        ];
    }
}
