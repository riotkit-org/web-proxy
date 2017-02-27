<?php declare(strict_types=1);

use DI\Container;
use Doctrine\Common\Cache\Cache;
use Proxy\Proxy;
use Psr\Log\LoggerInterface;
use Wolnosciowiec\WebProxy\Controllers\PassThroughController;
use Wolnosciowiec\WebProxy\Factory\ProxyClientFactory;
use Wolnosciowiec\WebProxy\Factory\ProxyProviderFactory;
use Wolnosciowiec\WebProxy\Factory\RequestFactory;
use Wolnosciowiec\WebProxy\Providers\Proxy\FreeProxyListProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;
use Wolnosciowiec\WebProxy\Service\Proxy\ProxySelector;

return [
    'config' => function () {
        return new \Doctrine\Common\Collections\ArrayCollection(require __DIR__ . '/../../config.php');
    },

    ProxySelector::class => function (Container $container) {
        return new ProxySelector($container->get(ProxyProviderInterface::class));
    },

    ProxyProviderFactory::class => function (Container $container) {

        /** @var \Doctrine\Common\Collections\ArrayCollection $config */
        $config = $container->get('config');

        return new ProxyProviderFactory(
            (string)$config->get('externalProxyProviders'),
            $container->get(Cache::class),
            $container
        );
    },

    RequestFactory::class => function () {
        return new RequestFactory();
    },

    ProxyClientFactory::class => function (Container $container) {
        return new ProxyClientFactory(
            $container->get(ProxySelector::class),
            (int)$container->get('config')->get('connectionTimeout')
        );
    },


    // controllers
    PassThroughController::class => function (Container $container) {
        return new PassThroughController(
            (int)$container->get('config')->get('maxRetries'),
            $container->get(ProxyClientFactory::class),
            $container->get(RequestFactory::class),
            $container->get(LoggerInterface::class)
        );
    },



    // providers
    FreeProxyListProvider::class => function (Container $container) {
        return new FreeProxyListProvider($container->get(Goutte\Client::class));
    },

    ProxyProviderInterface::class => function (Container $container) {
        return $container->get(ProxyProviderFactory::class)->create();
    },
];
