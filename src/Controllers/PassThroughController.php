<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Controllers;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use function GuzzleHttp\json_encode;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Wolnosciowiec\WebProxy\Factory\ProxyClientFactory;
use Wolnosciowiec\WebProxy\Factory\RequestFactory;

/**
 * @package Wolnosciowiec\WebProxy\Controllers
 */
class PassThroughController
{
    /**
     * @var int $retries
     */
    private $retries = 0;

    /**
     * @var int $maxRetries
     */
    private $maxRetries = 3;

    /**
     * @var ProxyClientFactory $clientFactory
     */
    private $clientFactory;

    /**
     * @var RequestFactory $requestFactory
     */
    private $requestFactory;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    public function __construct(
        int $maxRetries = 3,
        ProxyClientFactory $clientFactory,
        RequestFactory $requestFactory,
        LoggerInterface $logger
    ) {
        $this->requestFactory = $requestFactory;
        $this->maxRetries    = $maxRetries;
        $this->clientFactory = $clientFactory;
        $this->logger        = $logger;
    }

    /**
     * @throws \Exception
     * @return string
     */
    private function getRequestedURL()
    {
        if (!isset($_SERVER['HTTP_WW_TARGET_URL'])) {
            throw new \Exception('Request URL not specified. Should be in a header "WW_TARGET_URL"');
        }

        return $_SERVER['HTTP_WW_TARGET_URL'];
    }

    /**
     * @throws \Exception
     * @return \GuzzleHttp\Psr7\ServerRequest
     */
    private function getRequest()
    {
        return $this->requestFactory->create($this->getRequestedURL());
    }

    /**
     * @throws \Exception
     * @return Response
     */
    public function executeAction(): Response
    {
        try {
            $request = $this->getRequest();
            $request = $request->withProtocolVersion('1.1');

        } catch (\Exception $e) {
            $this->logger->error('Invalid request: ' . $e->getMessage());

            return new Response(400, [], json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
        }

        try {
            $this->logger->notice('Forwarding to "' . $this->getRequestedURL() . '"');

            // forward the request and get the response.
            $response = $this->clientFactory->create()
                ->forward($request)
                ->to($this->getRequestedURL());

        } catch (RequestException $e) {

            // try again in case of connection failure
            if (($e instanceof ConnectException || $e instanceof ServerException)
                && $this->maxRetries > $this->retries
            ) {
                $this->retries++;

                $this->logger->error('Retrying request(' . $this->retries . '/' . $this->maxRetries . ')');
                return $this->executeAction();
            }

            $response = $e->getResponse();

            if (!$response instanceof Response) {
                $response = new Response(500, [], $e->getMessage());
                $this->logger->notice('Error response: ' . $e->getMessage());
            }
        }

        return $this->fixResponseHeaders($response);
    }

    private function fixResponseHeaders(Response $response)
    {
        // fix: empty response if page is using gzip (Zend Diactoros is trying to do the same, but it's doing it incorrectly)
        if (!$response->hasHeader('Content-Length')) {
            $response = $response->withAddedHeader('Content-Length', strlen((string)$response->getBody()));
        }

        // we are not using any encoding at the output
        $response = $response->withoutHeader('Transfer-Encoding');

        return $response;
    }

    /**
     * @codeCoverageIgnore
     * @param int $code
     * @throws \InvalidArgumentException
     */
    public function sendResponseCode(int $code)
    {
        if (defined('IS_EMULATED_ENVIRONMENT') && IS_EMULATED_ENVIRONMENT) {
            return;
        }

        if ($code === 404) {
            header('HTTP/1.0 404 Not Found');
            return;
        }
        elseif ($code === 403) {
            header('HTTP 1.1 403 Unauthorized');
            return;
        }
        elseif ($code === 400) {
            header('HTTP/1.0 400 Bad Request');
            return;
        }

        throw new \InvalidArgumentException('Unrecognized code passed');
    }
}
