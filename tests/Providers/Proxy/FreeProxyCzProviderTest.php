<?php declare(strict_types=1);

namespace Tests\Providers\Proxy;

use Wolnosciowiec\WebProxy\Providers\Proxy\FreeProxyCzProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;

class FreeProxyCzProviderTest extends TestProxyProviderInterfaceImplementation
{
    /**
     * @return ProxyProviderInterface
     */
    protected function getProvider(): ProxyProviderInterface
    {
        return $this->getContainer()->get(FreeProxyCzProvider::class);
    }
}
