<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;

class ProxyListOrgProvider extends BaseProvider implements ProxyProviderInterface
{
    /**
     * @inheritdoc
     */
    public function collectAddresses(): array
    {
        $provider = $this;

        // connect
        $response = $this->client->request('GET', 'https://proxy-list.org/english/search.php?search=elite.ssl-yes&country=any&type=elite&port=any&ssl=yes');
        $content  = $response->html();

        // collect
        preg_match_all("/Proxy\('(.*)'\)/i", $content, $matches);
        $addresses = array_map(function ($row) { return base64_decode($row); }, $matches[1]);

        // build internal objects
        $addresses = array_map(function ($data) use ($provider) {

            $parts = explode(':', $data);

            if ((int)$parts[1] === 0) {
                return null;
            }

            $proxyAddress = new ProxyServerAddress();
            $proxyAddress->setAddress($parts[0]);
            $proxyAddress->setPort((int)$parts[1]);
            $proxyAddress->setSchema('https');

            return $proxyAddress;

        }, $addresses);

        return array_filter($addresses);
    }
}
