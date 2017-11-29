<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\Security;

use Blocktrail\CryptoJSAES\CryptoJSAES;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\Service\Config;

/**
 * Validates one-time tokens that are granted by
 * external application to browse a specific resource
 */
class OneTimeBrowseTokenChecker implements AuthCheckerInterface
{
    /**
     * @var string $encryptionKey
     */
    private $encryptionKey;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->encryptionKey = $config->get('encryptionKey');
    }

    /**
     * Checks only if encrypted one time token is valid
     *
     * @param ForwardableRequest $request
     * @return bool
     */
    public function isValid(ForwardableRequest $request): bool
    {
        try {
            $decrypted = CryptoJSAES::decrypt($request->getQueryParams()['__wp_one_time_token'] ?? '', $this->encryptionKey);
            $array = \GuzzleHttp\json_decode($decrypted, true);

            if (!isset($array['url'])) {
                return false;
            }

            // token can have expiration time
            if (isset($array['expires'])) {
                $expiration = new \DateTime($array['expires']);
                
                if ($expiration <= new \DateTime()) {
                    return false;
                }
            }

        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function canHandle(ForwardableRequest $request): bool
    {
        return isset($request->getQueryParams()['__wp_one_time_token']);
    }
}
