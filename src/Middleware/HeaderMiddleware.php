<?php
namespace App\Middleware;

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class HeaderMiddleware
{
    public function __invoke(Request $request, $handler)
    {
        /** @var Response $response */
        $response = $handler->handle($request);

        return $response
            ->withHeader('Access-Control-Allow-Origin', 'https://todonime.ru')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Credentials', 'true');
    }
}