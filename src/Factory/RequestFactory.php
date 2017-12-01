<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Factory;

use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\Exception\Codes;
use Wolnosciowiec\WebProxy\Exception\HttpException;
use Wolnosciowiec\WebProxy\InputParams;
use Zend\Diactoros\ServerRequest;
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
     * @throws HttpException
     * @return ForwardableRequest
     */
    public function create(string $destinationUrl): ForwardableRequest
    {
        // create a PSR7 request based on the current browser request.
        $request     = ServerRequestFactory::fromGlobals();
        $request     = $this->rewriteRequestToOwnRequest($request);
        $currentHost = $request->getUri()->getHost();

        $requestedUrl = new Uri($destinationUrl);
        $requestedUrl = $requestedUrl->withPath('');

        $request = $request->withUri($requestedUrl);

        if ($currentHost === $request->getUri()->getHost()) { // @codeCoverageIgnore
            throw new HttpException('Cannot make a request to the same host as we are'); // @codeCoverageIgnore
        }

        return $request;
    }

    /**
     * Creates the request object basing on globals (in this case it's a HTTP header accessible from global variable)
     *
     * @throws HttpException
     * @return ForwardableRequest
     */
    public function createFromGlobals(): ForwardableRequest
    {
        return $this->create((string) ($_SERVER['HTTP_WW_TARGET_URL'] ?? ''));
    }

    private function rewriteRequestToOwnRequest(ServerRequest $request): ForwardableRequest
    {
        return new ForwardableRequest(
            $_SERVER,
            $request->getUploadedFiles(),
            $request->getUri()->__toString(),
            $request->getMethod(),
            $request->getBody(),
            $request->getHeaders(),
            $request->getCookieParams(),
            $request->getQueryParams(),
            $request->getParsedBody(),
            $request->getProtocolVersion()
        );
    }
}
