<?php declare(strict_types=1);

namespace Tests\Providers\Proxy;

use Tests\TestCase;
use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;

/**
 * @package Tests\Providers\Proxy
 */
abstract class TestProxyProviderInterfaceImplementation extends TestCase
{
    /**
     * @return ProxyProviderInterface
     */
    abstract protected function getProvider(): ProxyProviderInterface;

    /**
     * @group integration
     */
    public function testCollectAddresses()
    {
        /**
         * @var ProxyProviderInterface $provider
         * @var ProxyServerAddress[]  $addresses
         */
        $provider = $this->getProvider();
        $addresses = $provider->collectAddresses();

        foreach ($addresses as $address) {
            $this->assertInstanceOf(ProxyServerAddress::class, $address);
            $this->assertRegExp('/(http|https)\:\/\/(.*)\:([0-9]+)/i', $address->getFormatted());
        }
    }
}