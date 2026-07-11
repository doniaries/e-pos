<?php
$dir = new RecursiveDirectoryIterator('app/Filament');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/', RecursiveRegexIterator::GET_MATCH);

$imports = [];
foreach($files as $file) {
    $content = file_get_contents($file[0]);
    if (preg_match_all('/use (Filament\\\\[^;]+);/', $content, $matches)) {
        foreach ($matches[1] as $match) {
            $imports[$match] = true;
        }
    }
}
$keys = array_keys($imports);
sort($keys);
foreach ($keys as $k) {
    echo "$k\n";
}
