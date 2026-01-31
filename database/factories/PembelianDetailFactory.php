<?php

namespace Database\Factories;

use App\Models\PembelianDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class PembelianDetailFactory extends Factory
{
    protected $model = PembelianDetail::class;

    public function definition(): array
    {
        $produkIds = \App\Models\Produk::pluck('id')->toArray();

        return [
            'pembelian_id' => \App\Models\Pembelian::inRandomOrder()->first()->id,
            'produk_id' => $produkIds[array_rand($produkIds)],
            'jumlah' => $this->faker->numberBetween(1, 100),
            'harga_beli' => function (array $attributes) {
                $produk = \App\Models\Produk::find($attributes['produk_id']);
                return $this->faker->numberBetween($produk->harga_beli * 0.8, $produk->harga_beli * 0.95);
            },
            'total_harga' => function (array $attributes) {
                return $attributes['jumlah'] * $attributes['harga_beli'];
            },
        ];
    }
}
