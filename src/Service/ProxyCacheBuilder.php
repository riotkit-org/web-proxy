<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Wolnosciowiec\WebProxy\Entity\ProxyServerAddress;
use Wolnosciowiec\WebProxy\Factory\ProxyProviderFactory;
use Wolnosciowiec\WebProxy\Providers\Proxy\CachedProvider;

/**
 * Builds cache for the CachedProvider
 *   Keeps the list fresh
 */
class ProxyCacheBuilder
{
    /**
     * @var ProxyProviderFactory $factory
     */
    private $factory;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var CachedProvider $provider
     */
    private $provider;

    /**
     * @var Client $client
     */
    private $client;

    /**
     * @var int $connectionTimeout
     */
    private $connectionTimeout;

    public function __construct(
        ProxyProviderFactory $factory,
        LoggerInterface $logger,
        CachedProvider $provider,
        Client $client,
        int $connectionTimeout = 10)
    {
        $this->factory           = $factory;
        $this->logger            = $logger;
        $this->provider          = $provider;
        $this->client            = $client;
        $this->connectionTimeout = $connectionTimeout;
    }

    /**
     * Fetches a new list
     *
     * @return ProxyServerAddress[]
     *
     * @throws \Exception
     */
    public function rebuildListCache(): array
    {
        $providers = $this->factory->buildProviders();
        $addresses = [];

        foreach ($providers as $provider) {
            $this->logger->info('Processing ' . get_class($provider));
            $addresses = array_merge($addresses, $provider->collectAddresses());
        }

        $this->logger->info('Saving ' . count($addresses) . ' addresses to cache');
        $this->provider->cacheResult($addresses);

        return $addresses;
    }

    /**
     * @param ProxyServerAddress[] $addresses
     */
    public function spawnVerificationProcesses(array $addresses)
    {
        foreach ($addresses as $address) {
            $command = '/bin/bash -c "' . __DIR__ . '/../../bin/verify-proxy-address ' . $address->getFormatted() . '" &';

            $this->logger->info('Spawning "' . $command . '"');
            passthru($command);
            sleep(1);
        }
    }

    /**
     * Connects to a proxy to check if the proxy is valid
     *
     * @param string $address
     *
     * @return bool
     */
    public function performProxyVerification(string $address): bool
    {
        if (!$address) {
            $this->logger->info('No address passed');
            return false;
        }

        $sitesToTest = [
            'https://duckduckgo.com/',
            'https://github.com/',
            'http://iwa-ait.org/'
        ];

        try {
            $this->client->request('GET', $sitesToTest[array_rand($sitesToTest)], [
                'proxy' => $address,
                'connect_timeout' => $this->connectionTimeout,
                'read_timeout'    => $this->connectionTimeout,
                'timeout'         => $this->connectionTimeout
            ]);
        } catch (ConnectException | RequestException | ClientException $exception) {
        	$this->logger->info('Exception: ' . $exception->getMessage());
            $this->logger->info('The proxy "' . $address . '" is not valid anymore, removing from cache');

            $this->removeFromCache($address);
            return false;
        }

        $this->logger->info('The proxy "' . $address . '" looks OK.');
        return true;
    }

    public function logSummary()
    {
        $this->logger->info('In the summary there are "' . \count($this->provider->getFromCache()) . ' working proxies');
    }

    private function removeFromCache(string $address)
    {
        $addresses = $this->provider->getFromCache();
        $withoutSpecificAddress = array_filter(
            $addresses,
            function (ProxyServerAddress $cached) use ($address) {
                return $cached->getFormatted() !== $address;
            }
        );

        $this->provider->cacheResult($withoutSpecificAddress);
    }
}
