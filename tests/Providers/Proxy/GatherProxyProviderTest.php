<?php declare(strict_types=1);

namespace Tests\Providers\Proxy;

use Wolnosciowiec\WebProxy\Providers\Proxy\GatherProxyProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;

class GatherProxyProviderTest extends TestProxyProviderInterfaceImplementation
{
    /**
     * @return ProxyProviderInterface
     */
    protected function getProvider(): ProxyProviderInterface
    {
        return $this->getContainer()->get(GatherProxyProvider::class);
    }
}
