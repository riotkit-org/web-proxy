<?php declare(strict_types=1);

use DI\Container;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Collections\ArrayCollection;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Wolnosciowiec\WebProxy\Controllers\ProxySelectorController;
use Wolnosciowiec\WebProxy\Controllers\RenderController;
use Wolnosciowiec\WebProxy\Service\Config;
use Wolnosciowiec\WebProxy\Controllers\PassThroughController;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\Service\ContentProcessor\{ContentProcessor, CssProcessor, HtmlProcessor};
use Wolnosciowiec\WebProxy\Service\FixturesManager;
use Wolnosciowiec\WebProxy\Service\Prerenderer;
use Wolnosciowiec\WebProxy\Service\Proxy\ProxySelector;
use Wolnosciowiec\WebProxy\Middleware\{
    ApplicationMiddleware, AuthenticationMiddleware, 
    OneTimeTokenParametersConversionMiddleware, ProxyStaticContentMiddleware
};
use Wolnosciowiec\WebProxy\Service\Security\{OneTimeBrowseTokenChecker, OneTimeTokenUrlGenerator, TokenAuthChecker};
use Wolnosciowiec\WebProxy\Factory\{ProxyClientFactory, ProxyProviderFactory, RequestFactory};

use Wolnosciowiec\WebProxy\Providers\Proxy\
{
    CachedProvider, ChainProvider, FreeProxyListProvider, GatherProxyProvider, HideMyNameProvider, ProxyListOrgProvider, ProxyProviderInterface
};

return [
    'config' => function () {
        return new ArrayCollection(require __DIR__ . '/../../config.php');
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
        /** @var ArrayCollection $config */
        $config = $container->get('config');

        return new ProxyProviderFactory(
            (string) $config->get('externalProxyProviders'),
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

    Prerenderer::class => function (Container $container) {
        return new Prerenderer(
            new Client(),
            (string) $container->get('config')->get('prerendererUrl')
        );
    },


    // controllers
    PassThroughController::class => function (Container $container) {
        return new PassThroughController(
            (int)$container->get('config')->get('maxRetries'),
            $container->get(ProxyClientFactory::class),
            $container->get(LoggerInterface::class),
            $container->get(FixturesManager::class)
        );
    },

    ProxySelectorController::class => function (Container $container) {
        return new ProxySelectorController($container->get(ProxySelector::class));
    },

    RenderController::class => function (Container $container) {
        return new RenderController(
            $container->get(ProxySelector::class),
            $container->get(Prerenderer::class),
            $container->get('config')->get('prerendererEnabled')
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

    CachedProvider::class => function (Container $container) {
        return new CachedProvider(
            $container->get(Cache::class),
            $container->get(ProxyProviderFactory::class)->create(),
            (int) ($container->get(Config::class)->get('cacheTtl') ?? 360)
        );
    },

    ProxyProviderInterface::class => function (Container $container) {
        return $container->get(CachedProvider::class);
    },

    ForwardableRequest::class => function (RequestFactory $factory) {
        return $factory->createFromGlobals();
    },
    
    AuthenticationMiddleware::class => function (Container $container) {
        return new AuthenticationMiddleware([
            $container->get(OneTimeBrowseTokenChecker::class),
            $container->get(TokenAuthChecker::class)
        ]);
    },

    OneTimeTokenParametersConversionMiddleware::class => function (Container $container) {
        return new OneTimeTokenParametersConversionMiddleware($container->get(Config::class));
    },
    
    ProxyStaticContentMiddleware::class => function (Container $container) {
        return new ProxyStaticContentMiddleware(
            $container->get(ContentProcessor::class),
            $container->get(Config::class)
        );
    },
    
    ApplicationMiddleware::class => function (Container $container) {
        return new ApplicationMiddleware(
            $container->get(PassThroughController::class),
            $container->get(ProxySelectorController::class),
            $container->get(RenderController::class)
        );
    },
    
    ContentProcessor::class => function (Container $container) {
        return new ContentProcessor([
            $container->get(HtmlProcessor::class),
            $container->get(CssProcessor::class)
        ]);
    },

    OneTimeBrowseTokenChecker::class => function (Container $container) {
        return new OneTimeBrowseTokenChecker($container->get(Config::class));
    },

    OneTimeTokenUrlGenerator::class => function (Container $container) {
        return new OneTimeTokenUrlGenerator($container->get(Config::class));
    },

    Config::class => function (Container $container) {
        return new Config(require __DIR__ . '/../../config.php');
    }
];
