<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\Service\Config;
use Wolnosciowiec\WebProxy\Service\ContentProcessor\ContentProcessor;
use Zend\Diactoros\CallbackStream;
use Zend\Diactoros\Stream;

/**
 * Passes all static content through proxy
 * by replacing all possible links in the output response
 */
class ProxyStaticContentMiddleware
{
    /**
     * @var ContentProcessor $processor
     */
    private $processor;

    /**
     * @var bool $enabled
     */
    private $enabled;

    /**
     * @param ContentProcessor $processor
     * @param Config           $config
     */
    public function __construct(ContentProcessor $processor, Config $config)
    {
        $this->processor = $processor;
        $this->enabled   = $config->getOptional('contentProcessingEnabled', false);
    }

    /**
     * @param ForwardableRequest $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @throws \Exception
     */
    public function __invoke(ForwardableRequest $request, ResponseInterface $response, callable $next)
    {
        $mimeType = $this->getMimeType($response);

        if (!$this->isEnabled($request) || !$this->processor->canProcess($mimeType)) {
            return $next($request, $response);
        }

        // append processed body
        $body = new Stream('php://temp', 'wb+');
        $body->write($this->processor->process($request, (string) $response->getBody(), $mimeType));
        $body->rewind();
        $response = $response->withBody($body);

        // add information helpful for debugging
        $response = $response->withHeader('X-Processed-With', 'Wolnosciowiec');

        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @return string
     */
    private function getMimeType(ResponseInterface $response)
    {
        $parts = explode(';', $response->getHeader('Content-Type')[0] ?? '');
        return strtolower($parts[0] ?? '');
    }

    /**
     * @param ForwardableRequest $request
     * @return bool
     */
    private function isEnabled(ForwardableRequest $request): bool
    {
        // in every request there must be a parameter explicitly defined
        if (!$request->canOutputBeProcessed()) {
            return false;
        }

        return $this->enabled;
    }
}
