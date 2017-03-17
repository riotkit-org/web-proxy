<?php declare(strict_types=1);

namespace Tests\Service\Proxy;

use PHPUnit\Framework\TestCase;
use Wolnosciowiec\WebProxy\Providers\Proxy\DummyProvider;
use Wolnosciowiec\WebProxy\Service\Proxy\ProxySelector;

/**
 * @see ProxySelector
 * @package Tests\Service\Proxy
 */
class ProxySelectorTest extends TestCase
{
    private function getValidProvider()
    {
        return new DummyProvider();
    }

    /**
     * @see ProxySelector::getHTTPProxy()
     */
    public function testGetHTTPProxy()
    {
        $proxySelector = new ProxySelector($this->getValidProvider());

        $this->assertRegExp('/https\:\/\/(.*)\:([0-9]+)/i', $proxySelector->getHTTPProxy());
    }

    /**
     * @see ProxySelector::getHTTPProxy()
     */
    public function testAddressesAreRandomlyReturned()
    {
        $proxySelector = new ProxySelector($this->getValidProvider());
        $addresses = [];

        // DummyProvider is providing at least 3 different addresses, the probability that only one of them
        // will be returned in 100000 iterations is near 0
        for ($i = 0; $i <= 100000; $i++) {
            $addresses[] = $proxySelector->getHTTPProxy();
        }

        $this->assertGreaterThan(2, array_unique($addresses));
    }
}
