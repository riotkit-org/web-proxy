<?php declare(strict_types=1);

use DI\Container;
use Doctrine\Common\Cache\Cache;
use Psr\Log\LoggerInterface;
use Wolnosciowiec\WebProxy\Controllers\PassThroughController;
use Wolnosciowiec\WebProxy\Factory\ProxyClientFactory;
use Wolnosciowiec\WebProxy\Factory\ProxyProviderFactory;
use Wolnosciowiec\WebProxy\Factory\RequestFactory;
use Wolnosciowiec\WebProxy\Providers\Proxy\FreeProxyListProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\GatherProxyProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\HideMyNameProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyListOrgProvider;
use Wolnosciowiec\WebProxy\Providers\Proxy\ProxyProviderInterface;
use Wolnosciowiec\WebProxy\Service\FixturesManager;
use Wolnosciowiec\WebProxy\Service\Proxy\ProxySelector;

return [
    'config' => function () {
        return new \Doctrine\Common\Collections\ArrayCollection(require __DIR__ . '/../../config.php');
    },

    FixturesManager::class => function (Container $container) {
        return new FixturesManager(
            $container->get('config')->get('fixtures'),
            $container->get('config')->get('fixtures_mapping')
        );
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
            $container->get(LoggerInterface::class),
            $container->get(FixturesManager::class)
        );
    },

    // providers
    FreeProxyListProvider::class => function (Container $container) {
        return new FreeProxyListProvider(
            $container->get(Goutte\Client::class),
            $container->get(LoggerInterface::class)
        );
    },

    HideMyNameProvider::class => function (Container $container) {
        return new HideMyNameProvider(
            $container->get(Goutte\Client::class),
            $container->get(LoggerInterface::class)
        );
    },

    GatherProxyProvider::class => function (Container $container) {
        return new GatherProxyProvider(
            $container->get(Goutte\Client::class),
            $container->get(LoggerInterface::class)
        );
    },

    ProxyListOrgProvider::class => function (Container $container) {
        return new ProxyListOrgProvider(
            $container->get(Goutte\Client::class),
            $container->get(LoggerInterface::class)
        );
    },

    ProxyProviderInterface::class => function (Container $container) {
        return $container->get(ProxyProviderFactory::class)->create();
    },
];
