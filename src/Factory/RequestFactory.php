<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Factory;

use GuzzleHttp\Psr7\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Uri;

/**
 * Builds the request to the destination server
 * --------------------------------------------
 *
 * @package Wolnosciowiec\WebProxy\Factory
 */
class RequestFactory
{
    /**
     * @param string $destinationUrl URL address we want to call and retrieve data from
     *
     * @throws \Exception
     * @return ServerRequest
     */
    public function create(string $destinationUrl)
    {
        // create a PSR7 request based on the current browser request.
        $request     = ServerRequestFactory::fromGlobals();
        $currentHost = $request->getUri()->getHost();

        $requestedUrl = new Uri($destinationUrl);
        $requestedUrl = $requestedUrl->withPath('');

        /** @var ServerRequest $request */
        $request = $request->withUri($requestedUrl);

        if ($currentHost === $request->getUri()->getHost()) { // @codeCoverageIgnore
            throw new \Exception('Cannot make a request to the same host as we are'); // @codeCoverageIgnore
        }

        // do the clean up before passing through the request
        $request = $request->withoutHeader('ww-target-url');
        $request = $request->withoutHeader('ww-token');

        return $request;
    }
}
