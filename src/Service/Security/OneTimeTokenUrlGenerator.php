<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\Security;

use Blocktrail\CryptoJSAES\CryptoJSAES;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\InputParams;
use Wolnosciowiec\WebProxy\Service\Config;

class OneTimeTokenUrlGenerator
{
    /**
     * @var array|float|int|string $encryptionKey
     */
    private $encryptionKey;

    /**
     * @var array|float|int|string $expirationTime
     */
    private $expirationTime;

    public function __construct(Config $config)
    {
        $this->encryptionKey  = $config->get('encryptionKey');
        $this->expirationTime = $config->getOptional('oneTimeTokenStaticFilesLifeTime', '+1 minute');
    }

    /**
     * @param ForwardableRequest $request
     * @param string $relativeOrAbsoluteUrl
     *
     * @return string
     */
    public function generateUrl(ForwardableRequest $request, string $relativeOrAbsoluteUrl)
    {
        $absoluteUrl = $this->makeAbsoluteUrl($request, $relativeOrAbsoluteUrl);

        $oneTimeToken = $this->encrypt([
            InputParams::ONE_TIME_TOKEN_PROPERTY_EXPIRES => (new \DateTime())->modify($this->expirationTime)->format('Y-m-d H:i:s'),
            InputParams::ONE_TIME_TOKEN_PROPERTY_URL     => $absoluteUrl,
            InputParams::ONE_TIME_TOKEN_PROCESS          => true,
            InputParams::ONE_TIME_TOKEN_STRIP_HEADERS    => $request->getDisallowedHeadersInResponse(),
        ]);

        return '?' . InputParams::QUERY_ONE_TIME_TOKEN . '=' . $oneTimeToken;
    }

    /**
     * @param array $data
     * @return string
     */
    private function encrypt(array $data): string
    {
        return CryptoJSAES::encrypt(json_encode($data), $this->encryptionKey);
    }

    /**
     * @param ForwardableRequest $request
     * @param string $relativeOrAbsoluteUrl
     *
     * @return string
     */
    private function makeAbsoluteUrl(ForwardableRequest $request, string $relativeOrAbsoluteUrl)
    {
        $absoluteUrlBeginsWith = [
            'http://', 'https://', '://',
        ];

        foreach ($absoluteUrlBeginsWith as $prefix) {
            if (strpos($relativeOrAbsoluteUrl, $prefix) === 0) {
                return $relativeOrAbsoluteUrl;
            }
        }

        $parsed  = parse_url($request->getDestinationUrl());
        $rootUrl = $parsed['scheme'] . '://' . $parsed['host'];

        if (isset($parsed['port'])) {
            $rootUrl .= ':' . $parsed['port'];
        }

        return $rootUrl . '/' . $relativeOrAbsoluteUrl;
    }
}
