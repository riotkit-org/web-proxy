<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Middleware;

use Blocktrail\CryptoJSAES\CryptoJSAES;
use Psr\Http\Message\ResponseInterface;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\Service\Config;

/**
 * Extract target URL from one-time token
 */
class OneTimeTokenParametersConversionMiddleware
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

    public function __invoke(ForwardableRequest $request, ResponseInterface $response, callable $next)
    {
        $oneTimeToken = $request->getQueryParams()['__wp_one_time_token'] ?? '';

        if (!$oneTimeToken) {
            return $next($request, $response);
        }

        $decrypted = CryptoJSAES::decrypt($oneTimeToken, $this->encryptionKey);
        $url = \GuzzleHttp\json_decode($decrypted, true)['url'] ?? '';

        return $next($request->withNewDestinationUrl($url), $response);
    }
}
