<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Entity;

use Wolnosciowiec\WebProxy\InputParams;
use Zend\Diactoros\ServerRequest;

class ForwardableRequest extends ServerRequest
{
    /**
     * @var string $forwardToUrl
     */
    private $forwardToUrl;

    /**
     * @var bool $processOutput
     */
    private $processOutput;

    /**
     * @var string $token
     */
    private $token;

    public function __construct(array $serverParams = [], array $uploadedFiles = [], $uri = null, ?string $method = null, $body = 'php://input', array $headers = [], array $cookies = [], array $queryParams = [], $parsedBody = null, string $protocol = '1.1')
    {
        parent::__construct($serverParams, $uploadedFiles, $uri, $method, $body, $headers, $cookies, $queryParams, $parsedBody, $protocol);
        $this->forwardToUrl = $headers[InputParams::HEADER_TARGET_URL][0] ?? '';
        $this->token = $headers[InputParams::HEADER_TOKEN][0] ?? ($queryParams[InputParams::QUERY_TOKEN] ?? '');
        $this->processOutput = count(
            array_filter([
                $headers[InputParams::HEADER_CAN_PROCESS][0] ?? '',
                $queryParams[InputParams::QUERY_CAN_PROCESS] ?? '',
            ])
        ) > 0;
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

    /**
     * @return bool
     */
    public function canOutputBeProcessed(): bool
    {
        return $this->processOutput;
    }

    /**
     * @param bool $processOutput
     * @return ForwardableRequest
     */
    public function withOutputProcessing(bool $processOutput): ForwardableRequest
    {
        $request = clone $this;
        $request->processOutput = $processOutput;

        return $request;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }
}
