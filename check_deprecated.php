<?php
$dir = new RecursiveDirectoryIterator('app/Filament/Resources');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/', RecursiveRegexIterator::GET_MATCH);

$deprecated = [
    'Filament\Pages\Actions' => 'Filament\Actions',
    'Filament\Resources\Form' => 'Filament\Forms\Form',
    'Filament\Resources\Table' => 'Filament\Tables\Table',
    'Filament\Tables\Filters\MultiSelectFilter' => 'Filament\Tables\Filters\SelectFilter (with ->multiple())',
    'Filament\Forms\Components\Card' => 'Filament\Forms\Components\Section',
    'Filament\Forms\Components\BelongsToSelect' => 'Filament\Forms\Components\Select',
    'Filament\Forms\Components\BelongsToManyMultiSelect' => 'Filament\Forms\Components\Select',
];

foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    foreach ($deprecated as $old => $new) {
        $search = str_replace('\\', '\\\\', $old);
        if (preg_match("/$search/", $content)) {
            echo "Found $old in $path\n";
        }
    }
}
