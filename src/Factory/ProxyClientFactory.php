<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Factory;

use GuzzleHttp\Client;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Proxy;
use Wolnosciowiec\WebProxy\Service\Proxy\ProxySelector;

/**
 * Creates a proxy client injecting
 * proxy server data (if available)
 * --------------------------------
 *
 * @package Wolnosciowiec\WebProxy\Factory
 */
class ProxyClientFactory
{
    /**
     * @var ProxySelector $proxySelector
     */
    private $proxySelector;

    /**
     * @var int $connectionTimeout
     */
    private $connectionTimeout = 10;

    public function __construct(ProxySelector $proxySelector, int $connectionTimeout = 10)
    {
        $this->proxySelector     = $proxySelector;
        $this->connectionTimeout = $connectionTimeout;
    }

    /**
     * @return Proxy
     */
    public function create()
    {
        return new Proxy(new GuzzleAdapter(
            new Client($this->getClientOptions())
        ));
    }

    /**
     * @return array
     */
    private function getClientOptions(): array
    {
        return array_filter([
            'proxy' => array_filter([
                'http'  => $this->proxySelector->getHTTPProxy(),
            ]),

            'connect_timeout' => $this->connectionTimeout,
            'read_timeout'    => $this->connectionTimeout,
            'timeout'         => $this->connectionTimeout,
        ]);
    }
}
