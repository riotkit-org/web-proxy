<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;

/**
 * @package Wolnosciowiec\WebProxy\Providers\Proxy
 */
class FreeProxyListProvider implements ProxyProviderInterface
{
    /**
     * @var Client $client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function collectAddresses(): array
    {
        $crawler = $this->client->request('GET', 'http://free-proxy-list.net');
        $rows = $crawler->filter('#proxylisttable tr');

        $addresses = $rows->each(function (Crawler $node) {
            $collection = $node->filter('td');

            // cells mapping
            $lastVerificationTime = @$collection->getNode(7)->textContent;
            $proxyType = @$collection->getNode(4)->textContent;
            $proxyPort = (int)@$collection->getNode(1)->textContent;
            $proxyIP   = @$collection->getNode(0)->textContent;
            $proxySchema =@ $collection->getNode(6)->textContent == 'yes' ? 'https' : 'http';

            if (!$proxyIP || !$proxyPort || strlen($lastVerificationTime) === 0 || !$proxyType) {
                return null;
            }

            if ($this->isEnoughFresh($lastVerificationTime) === false) {
                return null;
            }

            if ($proxyType != 'elite proxy') {
                return null;
            }

            $address = new ProxyServerAddress();
            $address->setAddress($proxyIP);
            $address->setPort($proxyPort);
            $address->setSchema($proxySchema);

            return $address;
        });

        return array_filter($addresses);
    }

    /**
     * @param string $time eg. "2 seconds", "1 minute", "2 minutes"
     * @return bool
     */
    private function isEnoughFresh(string $time): bool
    {
        $parts = explode(' ', $time);
        $multiply = 60 * 60 * 24 * 2;

        if (in_array($parts[1], ['minute', 'minutes'])) {
            $multiply = 60;
        }
        elseif (in_array($parts[1], ['hour', 'hours'])) {
            $multiply = 60 * 60;
        }
        elseif (in_array($parts[1], ['day', 'days'])) {
            $multiply = 60 * 60 * 24;
        }
        elseif (in_array($parts[1], ['second', 'seconds'])) {
            $multiply = 1;
        }

        return ((int)$parts[0] * $multiply) <= (60 * 5); // 5 minutes
    }
}
