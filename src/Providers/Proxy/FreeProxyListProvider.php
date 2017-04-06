<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Symfony\Component\DomCrawler\Crawler;
use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;

class FreeProxyListProvider extends BaseProvider implements ProxyProviderInterface
{
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
                $this->logger->critical('Error in data collection from free-proxy-list.net, cannot get IP or port or verification time or proxy type');
                return null;
            }

            if ($this->isEnoughFresh($lastVerificationTime) === false) {
                $this->logger->notice('[free-proxy-list.net] The proxy is old');
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
}
