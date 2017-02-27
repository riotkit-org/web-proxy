<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

/**
 * Allows to execute multiple providers
 * ------------------------------------
 *
 * @package Wolnosciowiec\WebProxy\Providers\Proxy
 */
class ChainProvider implements ProxyProviderInterface
{
    /** @var ProxyProviderInterface[] */
    private $providers;

    /**
     * @param ProxyProviderInterface[] $providers
     * @return $this
     */
    public function setProviders(array $providers = [])
    {
        $this->providers = $providers;
        return $this;
    }

    /**
     * @param array $providers
     */
    public function __construct(array $providers = [])
    {
        $this->setProviders($providers);
    }

    /**
     * @return array
     */
    public function collectAddresses(): array
    {
        $addresses = [];

        foreach ($this->providers as $provider) {
            $addresses = array_merge($addresses, $provider->collectAddresses());
        }

        return $addresses;
    }
}
