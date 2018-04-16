<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;

/**
 * @see http://us-proxy.org
 */
class UsProxyOrgProvider extends BaseProvider implements ProxyProviderInterface
{
    /**
     * @inheritdoc
     */
    public function collectAddresses(): array
    {
        $addresses = [];
        $crawler = $this->client->request('GET', 'https://www.us-proxy.org/', [], [], [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/51.0.2704.79 Chrome/51.0.2704.79 Safari/537.36'
        ]);
        $rowsInTheTable = $crawler->filterXPath('//table[@id="proxylisttable"]/tbody[1]/tr');

        if (!$rowsInTheTable->count()) {
            throw new \Exception('The crawler for us-proxy.org seems to not work anymore');
        }

        foreach ($rowsInTheTable as $element) {
            try {
                $data = [
                    'ip'     => trim($element->ownerDocument->saveHTML($element->childNodes[0])),
                    'port'   => trim($element->ownerDocument->saveHTML($element->childNodes[1])),
                    'https'  => trim($element->ownerDocument->saveHTML($element->childNodes[6])),
                    'last_checked' => trim($element->ownerDocument->saveHTML($element->childNodes[7]))
                ];

                $data = array_map(function (string $str) { return strip_tags($str); }, $data);

            } catch (\Throwable $exception) {
                $this->logger->debug($exception);
                continue;
            }

            if ($data['https'] !== 'yes' || !$this->isEnoughFresh($data['last_checked'])) {
                continue;
            }

            $address = new ProxyServerAddress();
            $address->setAddress($data['ip'])
                ->setPort((int) $data['port'])
                ->setSchema('https');

            $addresses[] = $address;
        }

        return $addresses;
    }
}
