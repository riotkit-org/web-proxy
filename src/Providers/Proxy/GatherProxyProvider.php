<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;

class GatherProxyProvider extends BaseProvider implements ProxyProviderInterface
{
    /**
     * @inheritdoc
     */
    public function collectAddresses(): array
    {
        $provider = $this;

        // connect
        $response = $this->client->request('GET', 'http://www.gatherproxy.com/');
        $content  = $response->html();

        // collect
        preg_match_all('/gp\.insertPrx\((.*)\)\;/i', $content, $matches);
        $addresses = array_map(function ($row) { return json_decode($row, true); }, $matches[1]);

        // build internal objects
        $addresses = array_map(function ($data) use ($provider) {

            if (!$provider->replacePort($data['PROXY_PORT'])) {
                $provider->logger->info('[GatherProxy] Unrecognized/unmapped port');
                return null;
            }

            if ($data['PROXY_TYPE'] !== 'Elite') {
                return null;
            }

            $proxyAddress = new ProxyServerAddress();
            $proxyAddress->setAddress($data['PROXY_IP']);
            $proxyAddress->setPort($this->replacePort($data['PROXY_PORT']));
            $proxyAddress->setSchema('http');

            return $proxyAddress;

        }, $addresses);

        return array_filter($addresses);
    }

    private function replacePort(string $port)
    {
        $mapping = [
            '1F90' => 8080,
            'C38'  => 3128,
            '50'   => 80,
            '22B8' => 8888,
            'C3A'  => 3130,
            '1F91' => 8081,
            '1FB6' => 8118,
            '115C' => 4444,
        ];

        return $mapping[$port] ?? '';
    }
}
