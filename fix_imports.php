<?php
$files = [
    'app/Filament/Resources/LaporanHarians/Pages/ListLaporanHarians.php',
    'app/Filament/Resources/Pelanggans/Tables/PelangganTable.php',
    'app/Filament/Resources/Produks/Pages/EditProduk.php',
    'app/Filament/Resources/Settings/Tables/SettingTable.php',
    'app/Filament/Resources/Stoks/Tables/StokTable.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    if (!preg_match('/use Filament\\\\Forms;/', $content)) {
        // Find namespace line
        $content = preg_replace('/(namespace\s+App[^;]+;)/', "$1\n\nuse Filament\\Forms;", $content);
        
        // Remove BOM if present
        if (substr($content, 0, 3) == "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }
        
        file_put_contents($file, $content);
        echo "Fixed $file\n";
    }
}
