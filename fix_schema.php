<?php
$file = 'c:/laragon/www/e-pos/app/Filament/Resources/Penjualans/Schemas/PenjualanSchema.php';
$content = file_get_contents($file);

// Add missing imports
$imports = <<<EOT
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Pelanggan;
use Filament\Notifications\Notification;
EOT;

$content = preg_replace('/use Filament\\\\Forms\\\\Form as Schema;/', "use Filament\\Forms\\Form as Schema;\n" . $imports, $content);

// Add recalculateTotal method before closing brace
$recalculateTotal = <<<EOT

    protected static function recalculateTotal(Forms\Set \$set, Forms\Get \$get): void
    {
        \$details = collect(\$get('details') ?? [])->toArray();
        \$subtotal = collect(\$details)->sum('subtotal');

        \$set('subtotal', \$subtotal);

        \$diskon_persen = \$get('diskon_persen') ?? 0;
        \$diskon_nilai = \$get('diskon_nilai') ?? 0;

        if (\$diskon_persen > 0) {
            \$diskon_nilai = round(\$subtotal * (\$diskon_persen / 100));
            \$set('diskon_nilai', \$diskon_nilai);
        }

        \$total_setelah_diskon = \$subtotal - \$diskon_nilai;

        \$pajak_persen = \$get('pajak_persen') ?? 0;
        \$pajak_nilai = round(\$total_setelah_diskon * (\$pajak_persen / 100));
        \$set('pajak_nilai', \$pajak_nilai);

        \$total = \$total_setelah_diskon + \$pajak_nilai;
        \$set('total', \$total);

        \$bayar = \$get('bayar') ?? 0;
        \$set('kembali', \$bayar - \$total);
    }
}
EOT;

$content = preg_replace('/}\s*}$/', $recalculateTotal, $content);

file_put_contents($file, $content);
