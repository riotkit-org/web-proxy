<?php declare(strict_types=1);

/*
 * Configuration file
 * ------------------
 *
 * Basically you do not have to edit this file, just provide values for the environment variablee
 */

$settings = [
    // general
    'apiKey'                   => getenv('WW_TOKEN') ? getenv('WW_TOKEN') : 'your-api-key-here',
    'externalProxyProviders'   => getenv('WW_EXTERNAL_PROXIES') !== false ? getenv('WW_EXTERNAL_PROXIES') : '',
    'cache'                    => new \Doctrine\Common\Cache\FilesystemCache(__DIR__ . '/var/cache'),
    'connectionTimeout'        => getenv('WW_TIMEOUT') !== false ? (int)getenv('WW_TIMEOUT') : 10,

    // post-process: fixtures are eg. adding headers, modifying responses, customizing things generally
    'fixtures'                 => getenv('WW_FIXTURES') !== false ? getenv('WW_FIXTURES') : '',
    'fixtures_mapping'         => getenv('WW_FIXTURES_MAPPING') !== false ? getenv('WW_FIXTURES_MAPPING') : '',

    // security: one-time-token is a possibility to grant access to the webproxy for a specific URL and until given time (url, expiration)
    'encryptionKey'            => getenv('WW_ENCRYPTION_KEY') !== false ? getenv('WW_ENCRYPTION_KEY') : 'your-encryption-key-here',
    'oneTimeTokenStaticFilesLifeTime' => getenv('WW_ONE_TIME_TOKEN_LIFE_TIME') !== false ? getenv('WW_ONE_TIME_TOKEN_LIFE_TIME') : '+60 minutes',

    // post-process: replacing external links with proxied links
    'contentProcessingEnabled' => getenv('WW_PROCESS_CONTENT') === '1' || getenv('WW_PROCESS_CONTENT') === false /* Enabled by default */,

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
