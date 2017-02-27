<?php declare(strict_types=1);

namespace Tests\Providers\Proxy;

use Wolnosciowiec\WebProxy\Providers\Proxy\ChainProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\DummyProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;

/**
 * @see ChainProvider
 * @package Tests\Providers\Proxy
 */
class ChainProviderTest extends TestProxyProviderInterfaceImplementation
{
    protected function getProvider(): ProxyProviderInterface
    {
        return new ChainProvider([
            new DummyProvider(),
            (new DummyProvider())->setMode(DummyProvider::RETURN_NONE),
        ]);
    }
}
