<?php
namespace App\Action\User;

use App\Action\Action;
use App\Helper\AuthHelper;
use App\Helper\ResponseHelper;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Logout extends Action
{
    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');

        if($user == null)
        {
            return ResponseHelper::success($response);
        }

        $authHelper = new AuthHelper($this->mongodb);
        $authHelper->logout($user['_id'], $request->getAttribute('token'));

        return ResponseHelper::success($response);
    }
}

