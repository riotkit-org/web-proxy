<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Goutte\Client;
use Psr\Log\LoggerInterface;
use Wolnosciowiec\WebProxy\Entity\TorProxyServerAddress;

/**
 * Adds a possibility to use a TOR proxy through eg. Privoxy
 */
class TorProxyProvider extends BaseProvider implements ProxyProviderInterface
{
    /**
     * @var array $torProxies
     */
    private $torProxies;

    /**
     * How many servers are going to be returned
     * This decides about the probability of Tor being chosen
     * when using Tor with other proxy providers
     *
     * @var int $serversNum
     */
    private $serversNum;

    public function __construct(array $torProxies, int $serversNum, Client $client, LoggerInterface $logger)
    {
        parent::__construct($client, $logger);
        $this->torProxies = array_filter($torProxies);
        $this->serversNum = $serversNum;
    }

    /**
     * @inheritdoc
     */
    public function collectAddresses(): array
    {
        if ($this->serversNum < 1 || !$this->getRandomServer()) {
            return [];
        }

        $proxies = [];

        foreach (range(1, $this->serversNum) as $num) {
            $virtualAddress = $this->getRandomServer();
            $split = explode('@', $virtualAddress);

            // example TOR proxy string: http://tor_proxy:8118@9051@some_passphrase
            $serverAddress     = $split[0];
            $torManagementPort = (int) ($split[1] ?? 9051);
            $passphrase        = $split[2] ?? '';

            $this->logger->info(
                'Registering TOR server "' . $serverAddress . '" with management port at ' . $torManagementPort
            );

            $address = new TorProxyServerAddress($torManagementPort, $passphrase);
            $address->setSchema('http')
                    ->setPort(parse_url($serverAddress, PHP_URL_PORT) ?: 80)
                    ->setAddress(parse_url($serverAddress, PHP_URL_HOST));

            $proxies[] = $address;
        }

        return $proxies;
    }

    private function getRandomServer(): string
    {
        if (!$this->torProxies) {
            return '';
        }

        return $this->torProxies[array_rand($this->torProxies)];
    }
}
