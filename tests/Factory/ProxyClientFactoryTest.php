<?php declare(strict_types=1);

namespace Tests\Factory;

use Proxy\Proxy;
use Tests\TestCase;
use Wolnosciowiec\WebProxy\Factory\ProxyClientFactory;
use Wolnosciowiec\WebProxy\Providers\Proxy\DummyProvider;
use Wolnosciowiec\WebProxy\Service\Proxy\ProxySelector;

/**
 * @see ProxyClientFactory
 * @package Tests\Factory
 */
class ProxyClientFactoryTest extends TestCase
{
    /**
     * @see ProxyClientFactory::create()
     */
    public function testCreate()
    {
        $selector = new ProxySelector(new DummyProvider());
        $clientFactory = new ProxyClientFactory($selector, 5);
        $guzzleProxy = $clientFactory->create();

        $this->assertInstanceOf(Proxy::class, $guzzleProxy);
    }
}
