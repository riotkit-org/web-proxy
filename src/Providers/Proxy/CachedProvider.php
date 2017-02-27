<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Doctrine\Common\Cache\Cache;

/**
 * Adds a cache layer to the proxy providers
 * -----------------------------------------
 *
 * @codeCoverageIgnore
 * @package Wolnosciowiec\WebProxy\Providers\Proxy
 */
class CachedProvider implements ProxyProviderInterface
{
    const CACHE_KEY = 'wolnosciowiec.webproxy.cache';

    /**
     * @var ProxyProviderInterface $provider
     */
    private $provider;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Cache $cache, ProxyProviderInterface $provider)
    {
        if ($provider instanceof $this) {
            throw new \InvalidArgumentException('Cannot cache self');
        }

        $this->cache    = $cache;
        $this->provider = $provider;
    }

    public function collectAddresses(): array
    {
        if ($this->cache->contains(self::CACHE_KEY)) {
            $addresses = $this->getFromCache();

            if (!empty($addresses)) {
                return $addresses;
            }
        }

        $addresses = $this->provider->collectAddresses();
        $this->cacheResult($addresses);

        return $addresses;
    }

    /**
     * @return array
     */
    private function getFromCache(): array
    {
        $data = unserialize($this->cache->fetch(self::CACHE_KEY));

        if ($data['expiration'] <= time()) {
            return [];
        }

        return $data['data'];
    }

    private function cacheResult(array $addresses)
    {
        $this->cache->save(self::CACHE_KEY, serialize([
            'data' => $addresses,
            'expiration' => time() + $this->getExpirationTime(),
        ]));
    }

    /**
     * @return int
     */
    protected function getExpirationTime(): int
    {
        return 10 * 60;
    }
}