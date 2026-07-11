<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$resources = glob("c:/laragon/www/e-pos/app/Filament/Resources/*/*Resource.php");
foreach ($resources as $res) {
    $content = file_get_contents($res);
    if (strpos($content, '$slug') === false) {
        $basename = basename($res, 'Resource.php');
        $slug = \Illuminate\Support\Str::kebab(\Illuminate\Support\Str::plural($basename));
        
        $content = preg_replace("/(protected static \?string \\\$model = .*?;)/", "$1\n    protected static ?string \$slug = '$slug';", $content);
        file_put_contents($res, $content);
        echo "Updated $basename -> $slug\n";
    }
}
