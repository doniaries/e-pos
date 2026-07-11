<?php
$file = 'c:/laragon/www/e-pos/app/Filament/Resources/Settings/Schemas/SettingSchema.php';
$content = file_get_contents($file);
$content = str_replace("])->columns(1),\n            ]);", "])->columns(1),\n                ]),\n            ]);", $content);
file_put_contents($file, $content);
