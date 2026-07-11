<?php
$files = [
    'app/Filament/Resources/Pelanggans/RelationManagers/PenjualansRelationManager.php',
    'app/Filament/Resources/Pelanggans/RelationManagers/PembayaranHutangRelationManager.php',
    'app/Filament/Resources/LaporanHarians/RelationManagers/PenjualansRelationManager.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Replace 'use Filament\Forms\Form;' with 'use Filament\Forms\Form as Schema;'
    $content = preg_replace('/use Filament\\\\Forms\\\\Form;/', 'use Filament\\Forms\\Form as Schema;', $content);
    
    // Replace 'public function form(Form $form): Form' with 'public function form(Schema $schema): Schema'
    $content = preg_replace('/public function form\(Form \$form\): Form/', 'public function form(Schema $schema): Schema', $content);
    
    // Replace '$form->' with '$schema->'
    $content = preg_replace('/\$form\s*->/', '$schema->', $content);
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
