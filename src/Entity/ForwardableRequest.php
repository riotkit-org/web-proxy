<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Entity;

use Zend\Diactoros\ServerRequest;

class ForwardableRequest extends ServerRequest
{
    /**
     * @var string $forwardToUrl
     */
    private $forwardToUrl;

    public function __construct(array $serverParams = [], array $uploadedFiles = [], $uri = null, ?string $method = null, $body = 'php://input', array $headers = [], array $cookies = [], array $queryParams = [], $parsedBody = null, string $protocol = '1.1')
    {
        parent::__construct($serverParams, $uploadedFiles, $uri, $method, $body, $headers, $cookies, $queryParams, $parsedBody, $protocol);
        $this->forwardToUrl = $headers['ww-target-url'][0] ?? '';
    }

    /**
     * Gets the URL we are forwarding request
     *
     * @return string
     */
    public function getDestinationUrl(): string
    {
        return $this->forwardToUrl;
    }

    /**
     * @param string $url
     * @return ForwardableRequest
     */
    public function withNewDestinationUrl(string $url): ForwardableRequest
    {
        $request = clone $this;
        $request->forwardToUrl = $url;

        return $request;
    }
}
