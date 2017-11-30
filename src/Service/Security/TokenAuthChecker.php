<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\Security;

use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\InputParams;

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
     * @inheritdoc
     */
    public function isValid(ForwardableRequest $request): bool
    {
        return in_array(
            $request->getToken(),
            $this->apiKeys,
            true
        );
    }

    /**
     * @inheritdoc
     */
    public function canHandle(ForwardableRequest $request): bool
    {
        return strlen($request->getToken()) > 0;
    }
}
