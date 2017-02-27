<?php declare(strict_types=1);

namespace Tests\Providers\Proxy;

use Wolnosciowiec\WebProxy\Providers\Proxy\FreeProxyListProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;

/**
 * @package Tests\Providers\Proxy
 */
class FreeProxyListProviderTest extends TestProxyProviderInterfaceImplementation
{
    /**
     * @return ProxyProviderInterface
     */
    protected function getProvider(): ProxyProviderInterface
    {
        return $this->getContainer()->get(FreeProxyListProvider::class);
    }
}
