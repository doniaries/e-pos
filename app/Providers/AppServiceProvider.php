<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
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
        // Rate limiter untuk API (dibutuhkan oleh throttleApi)
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        Produk::observe(ProdukObserver::class);
        \App\Models\Stok::observe(\App\Observers\StokObserver::class);
        \App\Models\PembelianDetail::observe(\App\Observers\PembelianDetailObserver::class);
        \App\Models\Penjualan::observe(\App\Observers\PenjualanObserver::class);
    }
}
