<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Prerenderer
{
    /**
     * @var Client $client
     */
    private $client;

    /**
     * @var string $serviceUrl
     */
    private $serviceUrl;

    /**
     * @param Client $client
     * @param string $prerenderUrl
     */
    public function __construct(Client $client, string $prerenderUrl)
    {
        $this->client     = $client;
        $this->serviceUrl = $prerenderUrl;
    }

    /**
     * Use Wolnościowiec Prerenderer service to fetch page contents
     *
     * @param string $url
     * @param string $proxyUrl
     *
     * @return string
     */
    public function render(string $url, string $proxyUrl): string
    {
        try {
            return $this->client->get($this->serviceUrl, [
                'headers' => [
                    'X-Render-Url'    => $url,
                    'X-Proxy-Address' => $proxyUrl
                ]
            ])->getBody()->getContents();

        } catch (RequestException $exception) {

            if (!$exception->getResponse()) {
                return 'Render error: ' . $exception->getMessage();
            }

            return 'Render error: ' . $exception->getResponse()->getBody()->getContents();
        }
    }
}
