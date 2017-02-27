<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\Proxy;

use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;

/**
 * Selects a HTTP/HTTPS proxy from provider
 * ----------------------------------------
 *
 * @package Wolnosciowiec\WebProxy\Service\Proxy
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

    /**
     * @return string
     */
    public function getHTTPProxy(): string
    {
        shuffle($this->addresses);

        foreach ($this->addresses as $address) {
            return $address->getFormatted();
        }

        return '';
    }
}
