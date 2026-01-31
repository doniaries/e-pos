<?php

namespace Database\Factories;

use App\Models\Pembelian;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PembelianFactory extends Factory
{
    protected $model = Pembelian::class;

    public function definition(): array
    {
        $distributors = \App\Models\Distributor::pluck('id')->toArray();

        return [
            'nomor_pembelian' => 'PB-' . $this->faker->unique()->regexify('[0-9]{8}'),
            'distributor_id' => $distributors[array_rand($distributors)],
            'tanggal_pembelian' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'total_harga' => $this->faker->numberBetween(100000, 5000000), // Ini nanti idealnya dihitung ulang dari detail
            'catatan' => $this->faker->optional()->paragraph,
            'created_by' => \App\Models\User::inRandomOrder()->first()->id ?? 1,
        ];
    }
}
