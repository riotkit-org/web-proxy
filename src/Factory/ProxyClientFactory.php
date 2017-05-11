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
    protected $proxySelector;

    /**
     * @var int $connectionTimeout
     */
    protected $connectionTimeout = 10;

    /**
     * @var array $options
     */
    protected $options = [];

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
    public function getClientOptions(): array
    {
        if (empty($this->options)) {
            $this->options = array_filter([
                'proxy' => array_filter([
                    'http'  => $this->proxySelector->getHTTPProxy(),
                ]),

                'connect_timeout' => $this->connectionTimeout,
                'read_timeout'    => $this->connectionTimeout,
                'timeout'         => $this->connectionTimeout,
            ]);
        }

        return $this->options;
    }

    /**
     * @return string
     */
    public function getProxyIPAddress(): string
    {
        return $this->getClientOptions()['proxy']['http'] ?? '';
    }
}
