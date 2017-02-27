<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Factory;

use DI\Container;
use Doctrine\Common\Cache\Cache;
use Wolnosciowiec\WebProxy\Providers\Proxy\CachedProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ChainProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;

/**
 * Constructs the provider object
 * ------------------------------
 *
 * @package Wolnosciowiec\WebProxy\Factory
 */
class ProxyProviderFactory
{
    /**
     * @var string $providersNames
     */
    private $providersNames = '';

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var Container $container
     */
    private $container;

    public function __construct(string $providerNames, Cache $cache, Container $container)
    {
        $this->providersNames = $providerNames;
        $this->cache          = $cache;
        $this->container      = $container;
    }

    public function create(): ProxyProviderInterface
    {
        $providers = new ChainProvider($this->buildProviders());
        return new CachedProvider($this->cache, $providers);
    }

    /**
     * @throws \Exception
     * @return ProxyProviderInterface[]
     */
    private function buildProviders()
    {
        $providers = [];
        $names = str_replace(' ', '', $this->providersNames);
        $names = array_filter(explode(',', $names));

        $defaultNamespace = '\\Wolnosciowiec\\WebProxy\\Providers\\Proxy\\';

        foreach ($names as $name) {
            if (class_exists($defaultNamespace . $name)) {
                $fullName = $defaultNamespace . $name;
            }
            elseif (class_exists($name)) {
                $fullName = $name;
            }
            else {
                throw new \Exception('Invalid provider name "' . $name . '", please check the configuration');
            }

            $providers[$fullName] = $this->container->get($fullName);
        }

        return $providers;
    }
}
