<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\Security;

use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\Service\Config;

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
    public function __construct(Config $config)
    {
        $keys = $config->get('apiKey');
        
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $this->apiKeys = $keys;
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
