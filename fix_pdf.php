<?php
$files = [
    'app/Filament/Resources/LaporanHarians/Pages/ListLaporanHarians.php',
    'app/Filament/Resources/LaporanHarians/Tables/LaporanHarianTable.php',
    'app/Filament/Resources/Pembelians/Tables/PembelianTable.php',
    'app/Filament/Resources/Produks/Tables/ProdukTable.php',
    'app/Filament/Resources/Stoks/Tables/StokTable.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Replace fn() => print(...)
    $content = preg_replace("/return response\(\)->streamDownload\(fn\(\) => print\(\\\$pdf->output\(\)\),\s*([^)]+)\);/", "return \\\$pdf->download($1);", $content);
    
    // Replace closure function() use ($pdf) { echo $pdf->output(); }
    $content = preg_replace("/return response\(\)->streamDownload\(function\s*\(\)\s*use\s*\(\\\$pdf\)\s*\{\s*(?:echo|print)\s*\\\$pdf->output\(\);\s*},\s*([^)]+)\);/s", "return \\\$pdf->download($1);", $content);
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
