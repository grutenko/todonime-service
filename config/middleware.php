<?php
return [
    new \App\Middleware\AuthMiddleware($container),
    function($request, $handler) {
        $response = $handler->handle($request);

        usleep(100000 - (microtime() - __START__));

        return $response;
    }
];