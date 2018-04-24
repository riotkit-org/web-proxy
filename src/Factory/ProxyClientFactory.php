<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Factory;

use GuzzleHttp\Client;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;
use Wolnosciowiec\WebProxy\Service\Proxy;
use Wolnosciowiec\WebProxy\Service\Proxy\ProxySelector;

/**
 * Creates a proxy client injecting
 * proxy server data (if available)
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
     * @param bool $withExternalProxy
     *
     * @return Proxy
     */
    public function create(bool $withExternalProxy = true)
    {
        return new Proxy(new GuzzleAdapter(
            new Client($this->getClientOptions($withExternalProxy))
        ));
    }

    /**
     * @param bool $withExternalProxy
     *
     * @return array
     */
    public function getClientOptions(bool $withExternalProxy): array
    {
        if (empty($this->options)) {
            $this->options = array_filter([
                'proxy' => $this->createConnectionAddressString($withExternalProxy, $this->proxySelector),
                'connect_timeout' => $this->connectionTimeout,
                'read_timeout'    => $this->connectionTimeout,
                'timeout'         => $this->connectionTimeout,
            ]);
        }

        return $this->options;
    }

    private function createConnectionAddressString(bool $withExternalProxy, ProxySelector $selector): ?string
    {
        if (!$withExternalProxy) {
            return null;
        }

        $address = $selector->getHTTPProxy();

        if ($address instanceof ProxyServerAddress) {
            $address->prepare();
            return $address->getFormatted();
        }

        return null;
    }

    /**
     * @param bool $withExternalProxy
     *
     * @return string
     */
    public function getProxyIPAddress(bool $withExternalProxy): string
    {
        return $this->getClientOptions($withExternalProxy)['proxy']['http'] ?? '';
    }
}
