<?php

namespace App\Filament\Widgets;

use App\Models\Shift;
use App\Helpers\ShiftHelper;
use Filament\Widgets\Widget;
use Filament\Notifications\Notification;
use Livewire\Attributes\Rule;

class ActiveShiftWidget extends Widget
{
    protected static string $view = 'filament.widgets.active-shift-widget';

    public static function canView(): bool
    {
        return ShiftHelper::shouldUseShift() && !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    protected int | string | array $columnSpan = 'full';

    public $activeShiftId;

    public function mount()
    {
        $this->activeShiftId = ShiftHelper::getActiveShiftId();

        if (!$this->activeShiftId) {
            $determined = ShiftHelper::determineShiftByTime();
            if ($determined) {
                $this->activeShiftId = $determined->id;
                ShiftHelper::setActiveShift($this->activeShiftId);
            }
        }
    }

    public function updatedActiveShiftId($value)
    {
        ShiftHelper::setActiveShift($value);

        $shift = Shift::find($value);
        $name = $shift ? $shift->nama : 'None';

        Notification::make()
            ->title('Shift Aktif Diperbarui')
            ->body("Sekarang bekerja pada: **{$name}**")
            ->success()
            ->send();

        return redirect(request()->header('Referer'));
    }

    protected function getViewData(): array
    {
        return [
            'shifts' => Shift::all(),
            'currentShift' => ShiftHelper::getActiveShift(),
        ];
    }
}
