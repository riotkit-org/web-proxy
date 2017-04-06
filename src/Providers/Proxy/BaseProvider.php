<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Providers\Proxy;

use Goutte\Client;
use Psr\Log\LoggerInterface;

abstract class BaseProvider
{
    /**
     * @var Client $client
     */
    protected $client;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    public function __construct(Client $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @param string $time eg. "2 seconds", "1 minute", "2 minutes"
     * @return bool
     */
    protected function isEnoughFresh(string $time): bool
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

        $this->logger->debug('Freshness: ' . (int)$parts[0] * $multiply);

        return ((int)$parts[0] * $multiply) <= (60 * 8); // 8 minutes
    }
}
