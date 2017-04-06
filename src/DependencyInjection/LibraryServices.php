<?php declare(strict_types=1);

use DI\Container;
use Doctrine\Common\Cache\Cache;
use Psr\Log\LoggerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

return [
    // external libraries
    Goutte\Client::class => function () {
        return new Goutte\Client();
    },

    Cache::class => function (Container $container) {
        $cache = $container->get('config')->get('cache');

        if (!$cache instanceof Cache) {
            throw new \Exception('"cache" configuration key should be an instance of \Doctrine\Common\Cache\Cache');
        }

        return $cache;
    },

    LoggerInterface::class => function () {
        $log = new Logger('wolnosciowiec.webproxy');
        $log->pushHandler(new StreamHandler(__DIR__ . '/../../var/app.log', Logger::INFO));

        if (PHP_SAPI === 'cli') {
            $log->pushHandler(new StreamHandler("php://stdout", Logger::DEBUG));
        }

        return $log;
    },
];
