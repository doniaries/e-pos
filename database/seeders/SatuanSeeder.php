<?php

namespace Database\Seeders;

use App\Models\Satuan;
use Illuminate\Database\Seeder;

class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            'PCS',
            'BKS',
            'PACK',
            'LUSIN',
            'KG',
            'ML',
            'LTR',
            'BOX',
            'SET',
            'UNIT',
            'ROLL',
            'MTR',
            'STRIP',
            'BTL',
            'SLOPE',
            'TIM',
            'RENTENG',
        ];

        foreach (array_unique($units) as $unit) {
            Satuan::create(['nama' => $unit]);
        }
    }
}
