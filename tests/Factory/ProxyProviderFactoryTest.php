<?php declare(strict_types=1);

namespace Tests\Factory;

use Doctrine\Common\Cache\Cache;
use Tests\TestCase;
use Wolnosciowiec\WebProxy\Factory\ProxyProviderFactory;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;

/**
 * @see ProxyProviderFactory
 * @package Wolnosciowiec\WebProxy\Factory
 */
class ProxyProviderFactoryTest extends TestCase
{
    /**
     * @param string $providerNames
     * @return ProxyProviderFactory
     */
    private function getFactory(string $providerNames = 'FreeProxyListProvider')
    {
        return new ProxyProviderFactory(
            $providerNames,
            $this->getContainer()
        );
    }

    /**
     * @see ProxyProviderFactory::create()
     */
    public function testCreate()
    {
        $provider = $this->getFactory()->create();

        // get chain provider from the inside
        $ref = new \ReflectionObject($provider);
        $property = $ref->getProperty('provider');
        $property->setAccessible(true);
        $chainProvider = $property->getValue($provider);

        // get a list of providers from the chain provider
        $ref = new \ReflectionObject($chainProvider);
        $property = $ref->getProperty('providers');
        $property->setAccessible(true);
        $providers = $property->getValue($chainProvider);

        $this->assertInstanceOf(ProxyProviderInterface::class, $provider);
        $this->assertInstanceOf(ProxyProviderInterface::class, $chainProvider);

        foreach ($providers as $provider) {
            $this->assertInstanceOf(ProxyProviderInterface::class, $provider);
        }
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid provider name "NonExistingProvider", please check the configuration
     */
    public function testValidationCreate()
    {
        $this->getFactory('NonExistingProvider')->create();
    }
}
