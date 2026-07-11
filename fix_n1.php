<?php
$files = [
    'c:/laragon/www/e-pos/app/Filament/Resources/Pembelians/Tables/PembelianTable.php' => "['distributor', 'creator', 'shift']",
    'c:/laragon/www/e-pos/app/Filament/Resources/PenjualanDetails/Tables/PenjualanDetailTable.php' => "['penjualan', 'produk', 'satuan']",
    'c:/laragon/www/e-pos/app/Filament/Resources/Penjualans/Tables/PenjualanTable.php' => "['kasir', 'pelanggan', 'shift']",
    'c:/laragon/www/e-pos/app/Filament/Resources/Produks/Tables/ProdukTable.php' => "['kategori_produk']",
    'c:/laragon/www/e-pos/app/Filament/Resources/Stoks/Tables/StokTable.php' => "['produk', 'user', 'shift']"
];

foreach ($files as $file => $with) {
    $content = file_get_contents($file);
    if (strpos($content, 'modifyQueryUsing') === false || $file == 'c:/laragon/www/e-pos/app/Filament/Resources/Pembelians/Tables/PembelianTable.php') {
        if (strpos($content, 'use Illuminate\Database\Eloquent\Builder;') === false) {
            $content = preg_replace("/(namespace .*?;)/", "$1\nuse Illuminate\Database\Eloquent\Builder;", $content);
        }
        
        if (strpos($content, "modifyQueryUsing(fn (Builder \$query)") === false) {
            $content = preg_replace("/(public static function table\(Table \\\$table\): Table\s*\{\s*return \\\$table)/s", "$1\n            ->modifyQueryUsing(fn (Builder \$query) => \$query->with($with))", $content);
        }
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
