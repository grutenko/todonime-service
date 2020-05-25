<?php

use App\Middleware\HeaderMiddleware;

require __DIR__ . '/../src/header.php';

/**
 * Загружаем глобальных посредников.
 */
foreach(require __DIR__ . '/../config/middleware.php' as $middleware) {
    $app->add($middleware);
}

$customErrorHandler = function (
    $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails,
    ?LoggerInterface $logger = null
) use ($app) {

    $payload = ['error' => $exception->getMessage(), 'error_descr' => $exception->getTrace()];

    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(
        json_encode($payload, JSON_UNESCAPED_UNICODE)
    );

    return $response;
};

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true, $logger);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);


$app->group('', function($group) use ($container) {
    require __DIR__ . '/../routes/video.php';
    require __DIR__ . '/../routes/anime.php';
    require __DIR__ . '/../routes/user.php';
});

$app->run();
