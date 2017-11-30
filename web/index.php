<?php declare(strict_types=1);

/*
 * Wolnościowiec / WebProxy
 * ------------------------
 *
 *   Web Proxy passing through all traffic on port 80
 *   A part of an anarchist portal - wolnosciowiec.net
 *
 *   Wolnościowiec is a project to integrate the movement
 *   of people who strive to build a society based on
 *   solidarity, freedom, equality with a respect for
 *   individual and cooperation of each other.
 *
 *   We support human rights, animal rights, feminism,
 *   anti-capitalism (taking over the production by workers),
 *   anti-racism, and internationalism. We negate
 *   the political fight and politicians at all.
 *
 *   http://wolnosciowiec.net/en
 *
 *   License: LGPLv3
 */

use Psr7Middlewares\Middleware;
use Relay\RelayBuilder;
use Wolnosciowiec\WebProxy\Exception\HttpException;
use Wolnosciowiec\WebProxy\Factory\RequestFactory;
use Wolnosciowiec\WebProxy\Middleware\
{
	ApplicationMiddleware, AuthenticationMiddleware, OneTimeTokenParametersConversionMiddleware, ProxyStaticContentMiddleware
};
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

/** @var \DI\Container $container */
$container = require __DIR__ . '/../src/bootstrap.php';

Middleware::setStreamFactory(function ($file, $mode) {
    return new Stream($file, $mode);
});

$relay = new RelayBuilder();
$dispatcher = $relay->newInstance([
	$container->get(AuthenticationMiddleware::class),
	$container->get(OneTimeTokenParametersConversionMiddleware::class),
	$container->get(ApplicationMiddleware::class),
	$container->get(ProxyStaticContentMiddleware::class)
]);

try {
    $request = $container->get(RequestFactory::class)->createFromGlobals();
    $response = $dispatcher(
        $request,
        new Response()
    );

} catch (HttpException $httpException) {
    $response = new Response\JsonResponse([
        'error' => $httpException->getMessage(),
        'code'  => $httpException->getCode(),
    ], 500);
}

$emitter = new Zend\Diactoros\Response\SapiEmitter();
$emitter->emit($response);
