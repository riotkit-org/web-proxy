<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Middleware;

use Blocktrail\CryptoJSAES\CryptoJSAES;
use Psr\Http\Message\ResponseInterface;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\InputParams;
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

    /**
     * @param ForwardableRequest $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return mixed
     */
    public function __invoke(ForwardableRequest $request, ResponseInterface $response, callable $next)
    {
        $oneTimeToken = $this->unescape($request->getQueryParams()[InputParams::QUERY_ONE_TIME_TOKEN] ?? '');

        if (!$oneTimeToken) {
            return $next($request, $response);
        }

        $decrypted = CryptoJSAES::decrypt($oneTimeToken, $this->encryptionKey);
        $decoded   = \GuzzleHttp\json_decode($decrypted, true);

        if ($decoded[InputParams::ONE_TIME_TOKEN_PROCESS] ?? false) {
            $request = $request->withOutputProcessing((bool) $decoded[InputParams::ONE_TIME_TOKEN_PROCESS]);
        }

        return $next(
            $request->withNewDestinationUrl($decoded[InputParams::ONE_TIME_TOKEN_PROPERTY_URL] ?? ''),
            $response
        );
    }

    /**
     * @param string $queryParameter
     * @return mixed
     */
    private function unescape(string $queryParameter)
    {
        return str_replace(' ', '+', $queryParameter);
    }
}
