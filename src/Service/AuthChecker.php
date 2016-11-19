<?php

namespace Wolnosciowiec\WebProxy\Service;

/**
 * Validates if the API token matches
 *
 * @package Wolnosciowiec\WebProxy\Service
 */
class AuthChecker
{
    /**
     * @var string[] $apiKey
     */
    private $apiKeys;

    public function __construct()
    {
        if (!is_file(__DIR__ . '/../../config.php')) {
            throw new \Exception('config.php file was not found');
        }

        $settings = require __DIR__ . '/../../config.php';

        if (!isset($settings['apiKey'])) {
            throw new \Exception('apiKey should be defined in config.php');
        }

        if (!is_array($settings['apiKey'])) {
            $settings['apiKey'] = [$settings['apiKey']];
        }

        $this->apiKeys = $settings['apiKey'];
    }

    /**
     * @param string $key
     * @return bool
     */
    public function validate($key)
    {
        return in_array($key, $this->apiKeys);
    }
}