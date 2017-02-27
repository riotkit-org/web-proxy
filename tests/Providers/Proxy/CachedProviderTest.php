<?php declare(strict_types=1);

namespace Tests\Providers\Proxy;

use Doctrine\Common\Cache\ArrayCache;
use Wolnosciowiec\WebProxy\Providers\Proxy\CachedProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ChainProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\DummyProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;

/**
 * @see CachedProvider
 * @package Tests\Providers\Proxy
 */
class CachedProviderTest extends TestProxyProviderInterfaceImplementation
{
    protected function getProvider(): ProxyProviderInterface
    {
        $chain = new ChainProvider([
            new DummyProvider(),
            (new DummyProvider())->setMode(DummyProvider::RETURN_NONE),
        ]);

        return new CachedProvider(new ArrayCache(), $chain);
    }

    /**
     * @see CachedProvider::collectAddresses()
     */
    public function testCollectAddressesTwice()
    {
        parent::testCollectAddresses();
    }
}
