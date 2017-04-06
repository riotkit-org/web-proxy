<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Symfony\Component\DomCrawler\Crawler;
use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;

class HideMyNameProvider extends BaseProvider implements ProxyProviderInterface
{
    /**
     * @inheritdoc
     */
    public function collectAddresses(): array
    {
        $crawler = $this->client->request('GET', 'https://hidemy.name/en/proxy-list/?maxtime=1000&type=s&anon=4#list');
        $rows = $crawler->filterXPath('//*[@id="content-section"]/section[1]/div/table/tbody/tr');

        $addresses = $rows->each(function (Crawler $node) {
            $collection = $node->filter('td');

            $proxyIP              = @$collection->getNode(0)->textContent;
            $proxyPort            = (int)@$collection->getNode(1)->textContent;
            $lastVerificationTime = @$collection->getNode(6)->textContent;

            if (!$proxyIP || !$proxyPort || strlen($lastVerificationTime) === 0) {
                $this->logger->critical('Error in data collection from hidemy.name, cannot get IP or port or verification time or proxy type');
                return null;
            }

            if (!$this->isEnoughFresh($lastVerificationTime)) {
                $this->logger->notice('[hidemy.name] The address is not enough fresh');
                return null;
            }

            $address = new ProxyServerAddress();
            $address->setAddress($proxyIP);
            $address->setPort($proxyPort);
            $address->setSchema('https');

            return $address;
        });

        return array_filter($addresses);
    }
}
