<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DateAndTimeWidget extends Widget
{
    public static function canView(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    protected static ?int $sort = -2; // Top position, beside AccountWidget (which is usually -3)

    protected static string $view = 'filament.widgets.date-and-time-widget';

    // Optional: make it span full width if desired, or keep it compact
    // protected int | string | array $columnSpan = 'full';
}
