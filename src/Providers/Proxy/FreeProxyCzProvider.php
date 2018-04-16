<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Goutte\Client;
use Psr\Log\LoggerInterface;
use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;

/**
 * @see http://free-proxy.cz
 */
class FreeProxyCzProvider extends BaseProvider implements ProxyProviderInterface
{
    /**
     * @var int $minUptime
     */
    private $minUptime;

    /**
     * @var int $maxPing
     */
    private $maxPing;

    public function __construct(Client $client, LoggerInterface $logger, int $minUptime = 80, int $maxPing = 350)
    {
        parent::__construct($client, $logger);
        $this->minUptime = $minUptime;
        $this->maxPing   = $maxPing;
    }

    /**
     * @inheritdoc
     */
    public function collectAddresses(): array
    {
        $addresses = [];
        $crawler = $this->client->request('GET', 'http://free-proxy.cz/en/proxylist/country/all/https/ping/level1/1', [], [], [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/51.0.2704.79 Chrome/51.0.2704.79 Safari/537.36'
        ]);
        $rowsInTheTable = $crawler->filterXPath('//table[@id="proxy_list"]/tbody[1]/tr');

        if (!$rowsInTheTable->count()) {
            throw new \Exception('The crawler for free-proxy.cz seems to not work anymore');
        }

        foreach ($rowsInTheTable as $element) {
            // 0 => IP Address
            // 1 => Port
            // 2 => Protocol
            // 8 => Uptime
            // 9 => Response

            try {
                $data = [
                    'ip'     => $element->ownerDocument->saveHTML($element->childNodes[0]),
                    'port'   => $element->ownerDocument->saveHTML($element->childNodes[1]),
                    'proto'  => $element->ownerDocument->saveHTML($element->childNodes[2]),
                    'uptime' => $element->ownerDocument->saveHTML($element->childNodes[8]),
                    'ping'   => $element->ownerDocument->saveHTML($element->childNodes[9])
                ];

                $data = array_map(function (string $str) { return strip_tags($str); }, $data);

            } catch (\Throwable $exception) {
                $this->logger->debug($exception);
                continue;
            }

            preg_match('/([0-9\.]+)\%/', $data['uptime'], $uptimeMatches);
            preg_match('/(\d+) ms/', $data['ping'], $pingMatches);

            if (!$uptimeMatches || (int) $uptimeMatches[1] < $this->minUptime) {
                $this->logger->debug(
                    $data['ip'] . ' has uptime < ' . $this->minUptime,
                    $uptimeMatches[1] ?? ''
                );
                continue;
            }

            if (!$pingMatches || (int) $pingMatches[1] > $this->maxPing) {
                $this->logger->debug(
                    $data['ip'] . ' has response time higher than ' . $this->maxPing . ' ms',
                    $pingMatches[1] ?? ''
                );
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
