<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Penjualan;
use App\Models\Setting;

class CetakStruk extends Component
{
    public $penjualan;
    public $setting;
    public $lebarKertas;
    public $isReprint = false;

    public function mount($id)
    {
        // Ambil data
        $this->penjualan = Penjualan::with(['details.produk', 'kasir', 'pelanggan'])->findOrFail($id);
        $this->setting = Setting::first();
        $this->isReprint = request()->query('reprint') == '1';

        // Logic lebar kertas
        $lebar = $this->setting->printer_lebar_kertas ?? '58mm';
        $this->lebarKertas = ($lebar === '80mm') ? '80mm' : '58mm';
    }

    public function render()
    {
        // PERUBAHAN DISINI:
        // Menggunakan view yang ada di folder 'resources/views/print/struk-thermal.blade.php'
        // Menggunakan layout yang ada di 'resources/views/layouts/empty.blade.php'
        // Pass safe title for PDF saving (replace invalid filename characters)
        $safeTitle = str_replace(['/', '\\'], '-', $this->penjualan->no_transaksi);
        
        return view('print.struk-thermal')
            ->layout('layouts.empty', ['title' => $safeTitle]);
    }
}
