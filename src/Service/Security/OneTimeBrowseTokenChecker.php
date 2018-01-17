<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\Security;

use Blocktrail\CryptoJSAES\CryptoJSAES;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\InputParams;
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

    private function unescape(string $queryParameter)
    {
        return str_replace(' ', '+', $queryParameter);
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
            $decrypted = CryptoJSAES::decrypt(
                $this->unescape($request->getQueryParams()[InputParams::QUERY_ONE_TIME_TOKEN] ?? ''), 
                $this->encryptionKey
            );

            $array = \GuzzleHttp\json_decode($decrypted, true);

            if (!isset($array[InputParams::ONE_TIME_TOKEN_PROPERTY_URL])) {
                return false;
            }

            // token can have expiration time
            if (isset($array[InputParams::ONE_TIME_TOKEN_PROPERTY_EXPIRES])) {
                $expiration = new \DateTime($array[InputParams::ONE_TIME_TOKEN_PROPERTY_EXPIRES]);
                
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
        return isset($request->getQueryParams()[InputParams::QUERY_ONE_TIME_TOKEN]);
    }
}
