<?php
$files = [
    'c:/laragon/www/e-pos/app/Filament/Resources/Pembelians/Tables/PembelianTable.php' => 'Pembelian',
    'c:/laragon/www/e-pos/app/Filament/Resources/Penjualans/Tables/PenjualanTable.php' => 'Penjualan',
];

foreach ($files as $file => $modelName) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Add imports
    $imports = [
        "use Filament\Forms;",
        "use Barryvdh\DomPDF\Facade\Pdf;",
        "use App\Models\\$modelName;"
    ];
    
    foreach ($imports as $import) {
        if (strpos($content, $import) === false) {
            $content = preg_replace('/(namespace .*?;)/', "$1\n$import", $content, 1);
        }
    }
    
    // Fix auth()->user()
    if (strpos($content, '/** @var \App\Models\User|null $user */') === false) {
        $content = str_replace(
            '$userName = auth()->user()?->name ?? \'System\';',
            "/** @var \App\Models\User|null \$user */\n                            \$user = auth()->user();\n                            \$userName = \$user?->name ?? 'System';",
            $content
        );
    }
    
    // Fix Pembelian::with -> \App\Models\Pembelian::with or just relies on the use statement we added
    file_put_contents($file, $content);
    echo "Fixed $file\n";
}
