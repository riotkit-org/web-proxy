<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;

interface ProxyProviderInterface
{
    /**
     * @return ProxyServerAddress[]
     */
    public function collectAddresses(): array;
}
