<?php declare(strict_types=1);

$settings = [
    'apiKey'                 => getenv('WW_TOKEN') ? getenv('WW_TOKEN') : 'your-api-key-here',
    'externalProxyProviders' => getenv('WW_EXTERNAL_PROXIES') !== false ? getenv('WW_EXTERNAL_PROXIES') : '',
    'cache'                  => new \Doctrine\Common\Cache\FilesystemCache(__DIR__ . '/var/cache'),
    'connectionTimeout'      => getenv('WW_TIMEOUT') !== false ? (int)getenv('WW_TIMEOUT') : 10,
    'fixtures'               => getenv('WW_FIXTURES') !== false ? getenv('WW_FIXTURES') : '',
    'fixtures_mapping'       => getenv('WW_FIXTURES_MAPPING') !== false ? getenv('WW_FIXTURES_MAPPING') : '',
    'encryptionKey'          => getenv('WW_ENCRYPTION_KEY') !== false ? getenv('WW_ENCRYPTION_KEY') : 'your-encryption-key-here',

    // examples
    #'externalProxyProviders' => 'FreeProxyListProvider',
    #'fixtures'               => 'FacebookCaptchaTo500',
];

if (is_file(__DIR__ . '/config.custom.php')) {
    $settings = array_merge(
        $settings,
        require __DIR__ . '/config.custom.php'
    );
}

return $settings;
