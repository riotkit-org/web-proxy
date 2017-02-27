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

use Wolnosciowiec\WebProxy\Controllers\PassThroughController;
use Wolnosciowiec\WebProxy\Service\AuthChecker;

$container = require __DIR__ . '/../src/bootstrap.php';

/** @var PassThroughController $controller */
$controller = $container->get(PassThroughController::class);
$auth       = new AuthChecker();
$token = ($_REQUEST['_token'] ?? ($_SERVER['HTTP_WW_TOKEN'] ?? ''));

if ($auth->validate($token) === false) {
    $controller->sendResponseCode(403);

    print(json_encode([
        'success' => false,
        'message' => '"_token" field does not contain a valid value',
    ]));

    exit;
}

$response = $controller->executeAction();
$emitter = new Zend\Diactoros\Response\SapiEmitter();
$emitter->emit($response);
