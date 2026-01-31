<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'nama' => 'Pagi',
                'jam_mulai' => '07:00:00',
                'jam_selesai' => '15:00:00',
            ],
            [
                'nama' => 'Sore',
                'jam_mulai' => '15:00:00',
                'jam_selesai' => '21:00:00',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                ['nama' => $shift['nama']],
                $shift
            );
        }
    }
}
