<?php
require __DIR__ . '/../src/header.php';

$app->addBodyParsingMiddleware();

$customErrorHandler = function (
    $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails,
    ?LoggerInterface $logger = null
) use ($app) {

    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(
        '500 ¯\_(ツ)_/¯ '.$exception->getMessage(). ' <pre>'. $exception->getTraceAsString() .'</pre>'
    );

    return $response;
};


// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true, $logger);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

$app->group('', function($group) {
    require __DIR__ . '/../routes/embed.php';
});

$app->run();
