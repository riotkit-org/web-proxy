<?php

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

if (getenv('WW_DEBUG')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Controllers/PassThroughController.php';
require __DIR__ . '/../src/Service/AuthChecker.php';

$controller = new \Wolnosciowiec\WebProxy\Controllers\PassThroughController();
$auth       = new \Wolnosciowiec\WebProxy\Service\AuthChecker();
$token = ($_REQUEST['_token'] ?? ($_SERVER['HTTP_WW_TOKEN'] ?? ''));

if ($auth->validate($token) === false) {
    $controller->sendResponseCode(403);

    print(json_encode([
        'success' => false,
        'message' => '"_token" field does not contain a valid value',
    ]));

    exit;
}

print($controller->executeAction());
