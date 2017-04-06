<?php declare(strict_types=1);

namespace Tests\Providers\Proxy;

use Wolnosciowiec\WebProxy\Providers\Proxy\GatherProxyProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyListOrgProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;

class ProxyListOrgProviderTest extends TestProxyProviderInterfaceImplementation
{
    /**
     * @return ProxyProviderInterface
     */
    protected function getProvider(): ProxyProviderInterface
    {
        return $this->getContainer()->get(ProxyListOrgProvider::class);
    }
}
