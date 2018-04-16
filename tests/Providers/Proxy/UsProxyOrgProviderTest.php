<?php declare(strict_types=1);

namespace Tests\Providers\Proxy;

use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;
use Wolnosciowiec\WebProxy\Providers\Proxy\UsProxyOrgProvider;

class UsProxyOrgProviderTest extends TestProxyProviderInterfaceImplementation
{
    /**
     * @return ProxyProviderInterface
     */
    protected function getProvider(): ProxyProviderInterface
    {
        return $this->getContainer()->get(UsProxyOrgProvider::class);
    }
}
