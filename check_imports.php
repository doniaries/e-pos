<?php
$dir = new RecursiveDirectoryIterator('app/Filament/Resources');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/', RecursiveRegexIterator::GET_MATCH);

foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    $needsForms = preg_match('/\bForms\\\\/', $content);
    $needsTables = preg_match('/\bTables\\\\/', $content);
    $hasForms = preg_match('/use Filament\\\\Forms;/', $content);
    $hasTables = preg_match('/use Filament\\\\Tables;/', $content);
    
    if ($needsForms && !$hasForms) {
        echo "Missing Forms: $path\n";
    }
    if ($needsTables && !$hasTables) {
        echo "Missing Tables: $path\n";
    }
}
