<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;

/**
 * Dummy implementation of the interface
 */
class DummyProvider implements ProxyProviderInterface
{
    const RETURN_VALID = 1;
    const RETURN_NONE  = 0;

    private $mode = self::RETURN_VALID;

    /**
     * @return array
     */
    public function collectAddresses(): array
    {
        if ($this->mode === self::RETURN_NONE) {
            return [];
        }

        return [
            (new ProxyServerAddress())
                ->setSchema('https')
                ->setPort(443)
                ->setAddress('localhost'),
            (new ProxyServerAddress())
                ->setSchema('http')
                ->setPort(80)
                ->setAddress('wolnosciowiec.local'),

            (new ProxyServerAddress())
                ->setSchema('http')
                ->setPort(8080)
                ->setAddress('zsp.net.pl'),
        ];
    }

    /**
     * @param int $mode
     * @return DummyProvider
     */
    public function setMode(int $mode): DummyProvider
    {
        $this->mode = $mode;
        return $this;
    }
}