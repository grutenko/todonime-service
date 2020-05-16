<?php
require __DIR__ . '/../src/header.php';

/**
 * Загружаем глобальных посредников.
 */
foreach(require __DIR__ . '/../config/middleware.php' as $middleware) {
    $app->add($middleware);
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
