<?php

namespace Wolnosciowiec\WebProxy\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Uri;
use Proxy\Proxy;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

/**
 * @package Wolnosciowiec\WebProxy\Controllers
 */
class PassThroughController
{
    /**
     * @throws \Exception
     * @return array
     */
    private function getRequestedURL()
    {
        if (!isset($_SERVER['HTTP_WW_TARGET_URL'])) {
            throw new \Exception('Request URL not specified. Should be in a header "WW_TARGET_URL"');
        }

        return $_SERVER['HTTP_WW_TARGET_URL'];
    }

    /**
     * @return ServerRequest
     * @throws \Exception
     */
    private function getRequest()
    {
        // create a PSR7 request based on the current browser request.
        $request     = ServerRequestFactory::fromGlobals();
        $currentHost = $request->getUri()->getHost();

        $requestedUrl = new Uri($this->getRequestedURL());
        $requestedUrl = $requestedUrl->withPath('');

        /** @var ServerRequest $request */
        $request = $request->withUri($requestedUrl);

        if ($currentHost === $request->getUri()->getHost()) { // @codeCoverageIgnore
            throw new \Exception('Cannot make a request to the same host as we are');
        }

        // do the clean up before passing through the request
        $request = $request->withoutHeader('ww-target-url');
        $request = $request->withoutHeader('ww-token');

        return $request;
    }

    /**
     * @return Proxy
     */
    private function getProxy()
    {
        return new Proxy(new GuzzleAdapter(new Client()));
    }

    /**
     * @throws \Exception
     * @return string
     */
    public function executeAction()
    {
        try {
            $request = $this->getRequest();
        }
        catch (\Exception $e) {
            $this->sendResponseCode(400);
            return json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        try {
            // forward the request and get the response.
            $response = $this->getProxy()
                ->forward($request)
                ->to($this->getRequestedURL());
        }
        catch (ConnectException $e) {
            $this->sendResponseCode(400);
            return json_encode([
                'success' => false,
                'message' => 'Connection error',
                'details' => $e->getMessage(),
            ]);
        }
        catch (ClientException $e) {
            $this->sendResponseCode(404);
            return json_encode([
                'success' => false,
                'message' => 'Got an ClientException',
                'details' => $e->getMessage(),
                'body'    => $e->getResponse()->getBody()->getContents(),
            ]);
        }

        return $response->getBody()->getContents();
    }

    /**
     * @codeCoverageIgnore
     * @param string $code
     * @throws \InvalidArgumentException
     */
    public function sendResponseCode($code)
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