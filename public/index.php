<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Psr7\Response;

require __DIR__. '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();

if(php_sapi_name() == 'cli') {
    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__);
}

if( in_array($_ENV['APP_ENV'], ['dev', 'local']) ) {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 'On');
}

/**
 * DEFINE API BASEDIR
 */
define('__API_DIR__', realpath(__DIR__ . '/../') );

if(! file_exists(__API_DIR__ . '/storage/log') ) {
    mkdir(__API_DIR__ . '/storage/log');
}
if(! file_exists(__API_DIR__ . '/storage/cache') ) {
    mkdir(__API_DIR__ . '/storage/cache');
}

$container = new Container();

// Set container to create App with on AppFactory
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addRoutingMiddleware();

$container = $app->getContainer();
require __DIR__ . '/../src/dependencies.php';

/**
 * Загружаем глобальных посредников.
 */
foreach(require __DIR__ . '/../config/middleware.php' as $middleware) {
    $app->add($middleware);
}

/**
 * Подключаем обработчики системных событий.
 */
foreach(require __DIR__ . '/../config/handler.php' as $key => $handler) {
    $container[$key] = $handler;
}

$app->group($_ENV['API_BASE'] ?: '/api', function($group) use ($container) {
    require __DIR__ . '/../routes/video.php';
    require __DIR__ . '/../routes/anime.php';
    require __DIR__ . '/../routes/user.php';

    $group->options('[{path:.*}]', function($request, $response) {
        /** @var Response $response */
        return $response->withHeader('Access-Control-Allow-Origin', '*');
    });
});

$app->group('/auth', function($group) use ($container) {
   require __DIR__ . '/../routes/auth.php';
});

$app->run();
