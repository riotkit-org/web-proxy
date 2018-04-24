<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\Proxy;

use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;

/**
 * Selects a HTTP/HTTPS proxy from provider
 */
class ProxySelector
{
    /**
     * @var ProxyServerAddress[] $addresses
     */
    private $addresses;

    public function __construct(ProxyProviderInterface $provider)
    {
        $this->addresses = $provider->collectAddresses();
    }

    public function getHTTPProxy(): ?ProxyServerAddress
    {
        if (!$this->addresses) {
            return null;
        }

        shuffle($this->addresses);
        return $this->addresses[0];
    }
}
