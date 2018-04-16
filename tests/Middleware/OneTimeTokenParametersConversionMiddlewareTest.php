<?php declare(strict_types=1);

namespace Tests\Middleware;

use Blocktrail\CryptoJSAES\CryptoJSAES;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\InputParams;
use Wolnosciowiec\WebProxy\Middleware\OneTimeTokenParametersConversionMiddleware;

/**
 * @see OneTimeTokenParametersConversionMiddleware
 */
class OneTimeTokenParametersConversionMiddlewareTest extends TestCase
{
    /**
     * @see OneTimeTokenParametersConversionMiddleware
     */
    public function test_token_properly_decoded()
    {
        $token = CryptoJSAES::encrypt(
            json_encode([
                InputParams::ONE_TIME_TOKEN_PROCESS => true,
                InputParams::ONE_TIME_TOKEN_PROPERTY_URL => 'http://iwa-ait.org'
            ]),

            'kick-off-bosses-power-to-the-grassroots-workers'
        );

        $middleware = new OneTimeTokenParametersConversionMiddleware('kick-off-bosses-power-to-the-grassroots-workers');
        $middleware(
            new ForwardableRequest(
                    [], [], 'http://localhost',
                    'GET', 'php://input', [], [], [
                    InputParams::QUERY_ONE_TIME_TOKEN => $token
                ]),
            new Response(),
            function (ForwardableRequest $request, $response) {
                $this->assertSame('http://iwa-ait.org', $request->getDestinationUrl());
                $this->assertTrue($request->canOutputBeProcessed());
            }
        );
    }

    /**
     * @see OneTimeTokenParametersConversionMiddleware
     */
    public function test_invalid_token_not_decoded()
    {
        $token = CryptoJSAES::encrypt(
            json_encode([
                InputParams::ONE_TIME_TOKEN_PROCESS => true,
                InputParams::ONE_TIME_TOKEN_PROPERTY_URL => 'http://zsp.net.pl'
            ]),

            'this-passphrase-is-different-than-the-server-uses'
        );

        $middleware = new OneTimeTokenParametersConversionMiddleware('long-live-anarchosyndicalism');
        $middleware(
            new ForwardableRequest(
                [], [], 'http://localhost',
                'GET', 'php://input', [], [], [
                InputParams::QUERY_ONE_TIME_TOKEN => $token
            ]),
            new Response(),
            function (ForwardableRequest $request, ResponseInterface $response) {
                $this->assertNull($request->getDestinationUrl());
                $this->assertFalse($request->canOutputBeProcessed());
                $this->assertSame(403, $response->getStatusCode());
            }
        );
    }
}
