<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Produk;
use App\Observers\ProdukObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Produk::observe(ProdukObserver::class);
        \App\Models\Stok::observe(\App\Observers\StokObserver::class);
        \App\Models\PembelianDetail::observe(\App\Observers\PembelianDetailObserver::class);
        \App\Models\Penjualan::observe(\App\Observers\PenjualanObserver::class);
    }
}
