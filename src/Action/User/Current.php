<?php


namespace App\Action\User;


use App\Helper\ResponseHelper;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Current extends \App\Action\Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if($user == null) {
            return ResponseHelper::error($response, 'USER_NOT_AUTH');
        }

        unset($user['token'], $user['auth_code']);

        return ResponseHelper::success($response, [
            'user' => $user
        ]);
    }
}