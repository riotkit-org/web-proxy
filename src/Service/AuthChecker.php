<?php declare(strict_types = 1);

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
        if (!is_file(__DIR__ . '/../../config.php')) {               // @codeCoverageIgnore
            throw new \Exception('config.php file was not found');   // @codeCoverageIgnore
        }

        $settings = require __DIR__ . '/../../config.php';

        if (!isset($settings['apiKey'])) {                                  // @codeCoverageIgnore
            throw new \Exception('apiKey should be defined in config.php'); // @codeCoverageIgnore
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
