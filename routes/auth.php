<?php
/** @var mixed $group */

use App\Action\Auth\AuthFromShikimori;

$group->get('/',  function(\Slim\Psr7\Request $request, $response) use($container) {
    $url = $container->get('shikimori_sdk')->auth()
        ->generateAuthUrl('https://auth.todonime.ru/complete');

    $params = $request->getQueryParams();

    $response = $response
        ->withStatus(302)
        ->withAddedHeader('Location', $url);

    return isset($params['back_url'])
        ? $response->withHeader("Set-Cookie", "auth_back_url={$params['back_url']}; Path=/; Max-Age=3600")
        : $response;
});

$group->get('/complete', AuthFromShikimori::class);