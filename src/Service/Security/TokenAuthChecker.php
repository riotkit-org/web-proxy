<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\Security;

use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;

/**
 * Validates if the API token matches
 */
class TokenAuthChecker implements AuthCheckerInterface
{
    /**
     * @var string[] $apiKey
     */
    private $apiKeys;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        if (!is_file(__DIR__ . '/../../../config.php')) {            // @codeCoverageIgnore
            throw new \Exception('config.php file was not found');   // @codeCoverageIgnore
        }

        $settings = require __DIR__ . '/../../../config.php';

        if (!isset($settings['apiKey'])) {                                  // @codeCoverageIgnore
            throw new \Exception('apiKey should be defined in config.php'); // @codeCoverageIgnore
        }

        if (!is_array($settings['apiKey'])) {
            $settings['apiKey'] = [$settings['apiKey']];
        }

        $this->apiKeys = $settings['apiKey'];
    }

    /**
     * @param ForwardableRequest $request
     * @return bool
     */
    public function isValid(ForwardableRequest $request): bool
    {
        return in_array(
            $request->getQueryParams()['__wp_token'] ?? ($request->getServerParams()['HTTP_WW_TOKEN'] ?? ''),
            $this->apiKeys,
            true
        );
    }

    /**
     * @inheritdoc
     */
    public function canHandle(ForwardableRequest $request): bool
    {
        return isset($request->getServerParams()['HTTP_WW_TOKEN'])
               || isset($request->getQueryParams()['__wp_token']);
    }
}
