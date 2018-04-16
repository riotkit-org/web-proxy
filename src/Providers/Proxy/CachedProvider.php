<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Doctrine\Common\Cache\Cache;

/**
 * Adds a cache layer to the proxy providers
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

    /**
     * @var int $ttl
     */
    private $ttl;

    public function __construct(Cache $cache, ProxyProviderInterface $provider, int $ttl = 360)
    {
        if ($provider instanceof $this) {
            throw new \InvalidArgumentException('Cannot cache self');
        }

        $this->cache    = $cache;
        $this->provider = $provider;
        $this->ttl      = $ttl;
    }

    /**
     * @inheritdoc
     */
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

    public function getFromCache(): array
    {
        $data = unserialize($this->cache->fetch(self::CACHE_KEY));

        if ($data['expiration'] <= time()) {
            return [];
        }

        return $data['data'];
    }

    public function cacheResult(array $addresses)
    {
        $this->cache->save(self::CACHE_KEY, serialize([
            'data' => $addresses,
            'expiration' => time() + $this->getExpirationTime(),
        ]));
    }

    protected function getExpirationTime(): int
    {
        return $this->ttl;
    }
}
