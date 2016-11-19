<?php

$settings = [
    'apiKey' => getenv('WW_TOKEN') ? getenv('WW_TOKEN') : 'your-api-key-here',
];

if (is_file(__DIR__ . '/config.custom.php')) {
    $settings = array_merge(
        $settings,
        require __DIR__ . '/config.custom.php'
    );
}

return $settings;