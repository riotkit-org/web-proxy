<?php declare(strict_types=1);

// @codeCoverageIgnoreStart
use DI\Definition\Source\DefinitionFile;

if (getenv('WW_DEBUG')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Controllers/PassThroughController.php';

$builder = new DI\ContainerBuilder();
$builder->useAnnotations(false);
$builder->useAutowiring(true);
$builder->addDefinitions(new DefinitionFile(__DIR__ . '/../src/DependencyInjection/Services.php'));
$builder->addDefinitions(new DefinitionFile(__DIR__ . '/../src/DependencyInjection/LibraryServices.php'));
$container = $builder->build();

// for PhpUnit
$GLOBALS['container'] = $container;

return $container;

// @codeCoverageIgnoreEnd
