<?php

namespace App\Filament\Widgets;

use App\Models\Setting;
use Filament\Widgets\Widget;
use Filament\Notifications\Notification;

class StoreStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.store-status-widget';

    protected static ?int $sort = -10; // Place it at the top

    protected int | string | array $columnSpan = 'full';

    public $isTutup;
    public $pesanTutup;

    public function mount()
    {
        $setting = Setting::first();
        $this->isTutup = $setting?->is_toko_tutup ?? false;
        $this->pesanTutup = $setting?->pesan_tutup ?? 'Toko sedang libur, silakan kembali lagi nanti.';
    }

    public function toggleStatus()
    {
        $setting = Setting::first();
        if ($setting) {
            $this->isTutup = !$this->isTutup;
            $setting->update([
                'is_toko_tutup' => $this->isTutup,
            ]);

            Notification::make()
                ->title($this->isTutup ? 'Toko berhasil DITUTUP' : 'Toko berhasil DIBUKA')
                ->success()
                ->send();
        }
    }

    public function updatePesan()
    {
        $setting = Setting::first();
        if ($setting) {
            $setting->update([
                'pesan_tutup' => $this->pesanTutup,
            ]);

            Notification::make()
                ->title('Pesan libur diperbarui')
                ->success()
                ->send();
        }
    }
}
