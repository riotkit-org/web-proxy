<?php declare(strict_types=1);

$settings = [
    'apiKey'                 => getenv('WW_TOKEN') ? getenv('WW_TOKEN') : 'your-api-key-here',
    'externalProxyProviders' => getenv('WW_EXTERNAL_PROXIES') !== false ? getenv('WW_EXTERNAL_PROXIES') : '',
    'cache'                  => new \Doctrine\Common\Cache\FilesystemCache(__DIR__ . '/var/cache'),
    'connectionTimeout'      => getenv('WW_TIMEOUT') !== false ? (int)getenv('WW_TIMEOUT') : 10,

    // examples
    #'externalProxyProviders' => 'FreeProxyListProvider',
];

if (is_file(__DIR__ . '/config.custom.php')) {
    $settings = array_merge(
        $settings,
        require __DIR__ . '/config.custom.php'
    );
}

return $settings;