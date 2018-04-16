<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Controllers;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Wolnosciowiec\WebProxy\Service\Prerenderer;
use Wolnosciowiec\WebProxy\Service\Proxy\ProxySelector;

/**
 * Renders the page content using the external service
 */
class RenderController extends BaseController
{
    /**
     * @var ProxySelector $proxySelector
     */
    private $proxySelector;

    /**
     * @var Prerenderer $prerenderer
     */
    private $prerenderer;

    /**
     * @var bool $enabled
     */
    private $enabled;

    public function __construct(
        ProxySelector $proxySelector,
        Prerenderer $prerenderer,
        bool $enabled)
    {
        $this->proxySelector = $proxySelector;
        $this->prerenderer   = $prerenderer;
        $this->enabled       = $enabled;
    }

    public function executeAction(RequestInterface $request): ResponseInterface
    {
        if (!$this->enabled) {
            return new Response(500, [], 'The prerender functionality was not enabled.');
        }

        $proxyAddress = !$this->hasDisabledExternalProxy($request) ? $this->proxySelector->getHTTPProxy() : '';
        $targetUrl    = (string) $request->getHeader('ww-url')[0] ?? '';

        $response = new Response(
            200,
            [
                'X-Wolnosciowiec-Proxy' => $proxyAddress,
                'X-Target-Url'          => $targetUrl
            ],
            $this->prerenderer->render($targetUrl, $proxyAddress)
        );

        return $this->validateResponse($response);
    }

    private function validateResponse(ResponseInterface $response): ResponseInterface
    {
        if (trim($response->getBody()->getContents()) === '<html><head></head><body></body></html>') {
            return new Response(503, $response->getHeaders(), 'Proxy error, got empty HTML');
        }

        return $response;
    }
}
