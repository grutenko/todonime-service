<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Psr7\Response;
use Dotenv\Dotenv;

require __DIR__. '/../vendor/autoload.php';
Dotenv
    ::createImmutable(__DIR__ . '/../')
    ->load();

if(php_sapi_name() == 'cli') {
    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__. '/../public');
}

if( in_array($_ENV['APP_ENV'], ['dev', 'local']) ) {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 'On');
}

/**
 * DEFINE API BASEDIR
 */
define('__API_DIR__', realpath(__DIR__ . '/../') );

AppFactory::setContainer(new Container());
$app = AppFactory::create();
$app->addRoutingMiddleware();

$container = $app->getContainer();
require __DIR__ . '/dependencies.php';

/**
 * Подключаем обработчики системных событий.
 */
foreach(require __DIR__ . '/../config/handler.php' as $key => $handler) {
    $container[$key] = $handler;
}
