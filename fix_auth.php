<?php
$files = [
    'c:/laragon/www/e-pos/app/Filament/Resources/Pembelians/Tables/PembelianTable.php',
    'c:/laragon/www/e-pos/app/Filament/Resources/Penjualans/Tables/PenjualanTable.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Replace auth()->user() with \Illuminate\Support\Facades\Auth::user()
    $content = str_replace('auth()->user()', '\Illuminate\Support\Facades\Auth::user()', $content);
    
    file_put_contents($file, $content);
    echo "Fixed $file\n";
}
