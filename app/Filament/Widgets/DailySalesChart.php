<?php

namespace App\Filament\Widgets;

use App\Models\Penjualan;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class DailySalesChart extends ChartWidget
{
    public static function canView(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    protected static ?string $heading = 'Penjualan Harian (30 Hari Terakhir)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Trend::model(Penjualan::class)
            ->between(
                start: now()->subDays(30),
                end: now(),
            )
            ->perDay()
            ->sum('total');

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
