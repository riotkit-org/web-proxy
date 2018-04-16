<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Controllers;

use GuzzleHttp\Exception\{ConnectException, RequestException, ServerException};
use function GuzzleHttp\json_encode;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\Exception\Codes;
use Wolnosciowiec\WebProxy\Exception\HttpException;
use Wolnosciowiec\WebProxy\Factory\ProxyClientFactory;
use Wolnosciowiec\WebProxy\Service\FixturesManager;
use Zend\Diactoros\Response\JsonResponse;

class PassThroughController extends BaseController
{
    /**
     * @var int $retries
     */
    protected $retries = 0;

    /**
     * @var int $maxRetries
     */
    protected $maxRetries = 3;

    /**
     * @var ProxyClientFactory $clientFactory
     */
    protected $clientFactory;

    /**
     * @var ForwardableRequest $request
     */
    protected $request;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var FixturesManager $fixturesManager
     */
    protected $fixturesManager;

    public function __construct(
        int $maxRetries = 3,
        ProxyClientFactory $clientFactory,
        LoggerInterface $logger,
        FixturesManager $fixturesManager
    ) {
        $this->maxRetries      = $maxRetries;
        $this->clientFactory   = $clientFactory;
        $this->logger          = $logger;
        $this->fixturesManager = $fixturesManager;
    }

    /**
     * @param ForwardableRequest $request
     * @throws HttpException
     * @return string
     */
    private function getRequestedURL(ForwardableRequest $request)
    {
        $url = $request->getDestinationUrl();

        if (!$url) {
            throw new HttpException('Missing target URL, did you provided a one-time token, WW-URL header or WW_URL environment variable?', Codes::HTTP_MISSING_URL);
        }

        return $url;
    }

    /**
     * @param ForwardableRequest $request
     * @throws \Exception
     * @return Response
     */
    public function executeAction(ForwardableRequest $request): ResponseInterface
    {
        try {
            $request = $request->withProtocolVersion('1.1');

        } catch (\Exception $e) {
            $this->logger->error('Invalid request: ' . $e->getMessage());

            return new Response(400, [], json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
        }

        try {
            $this->logger->notice('Forwarding to "' . $this->getRequestedURL($request) . '"');

            // forward the request and get the response.
            $response = $this->clientFactory->create(!$this->hasDisabledExternalProxy($request))
                ->forward($request)
                ->to($this->getRequestedURL($request));

            $response = $response->withHeader('X-Wolnosciowiec-Proxy', $this->clientFactory->getProxyIPAddress(!$this->hasDisabledExternalProxy($request)));

        } catch (RequestException $e) {

            // try again in case of connection failure
            if (
                ($e instanceof ConnectException || $e instanceof ServerException)
                && $this->maxRetries > $this->retries
            ) {
                $this->retries++;

                $this->logger->error('Retrying request(' . $this->retries . '/' . $this->maxRetries . ')');
                return $this->executeAction($request);
            }

            $response = $e->getResponse();

            if (!$response instanceof Response) {
                $response = new JsonResponse(['error' => $e->getMessage()], 500);
                $this->logger->notice('Error response: ' . $e->getMessage());
            }
        }

        // apply fixtures
        $response = $this->fixturesManager->fix($request, $response);

        // add optional headers
        $response = $response->withHeader('X-Target-Url', $request->getDestinationUrl());
        $response = $response->withHeader('X-Powered-By', 'Wolnosciowiec WebProxy');

        return $this->fixResponseHeaders($response);
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface|static
     */
    private function fixResponseHeaders(ResponseInterface $response)
    {
        // fix: empty response if page is using gzip (Zend Diactoros is trying to do the same, but it's doing it incorrectly)
        if (!$response->hasHeader('Content-Length')) {
            $response = $response->withAddedHeader('Content-Length', strlen((string)$response->getBody()));
        }

        // we are not using any encoding at the output
        $response = $response->withoutHeader('Transfer-Encoding');

        return $response;
    }
}
