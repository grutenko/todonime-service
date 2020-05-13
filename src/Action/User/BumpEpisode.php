<?php


namespace App\Action\User;


use App\Action\Action;
use App\Helper\ResponseHelper;
use App\Lib\Queue\Client;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use function GuzzleHttp\Psr7\parse_query;

class BumpEpisode extends Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        if($request->getAttribute('user') == null) {
            return ResponseHelper::error($response, 'USER_NOT_AUTH', '', 403);
        }

        $user = $request->getAttribute('user');
        $params = parse_query($request->getBody()->getContents());

        if(!isset($params['anime_id']) || !isset($params['episode'])) {
            return ResponseHelper::error($response, 'PARAMS_ERROR', '', 400);
        }

        $queue = new Client($this->mongodb->todonime->queue);

        $queue->add([
            'worker' => 'bump_episode',
            'anime_id' => $params['anime_id'],
            'user_id' => $user['_id']->__toString(),
            'episode' => $params['episode']
        ]);

        return ResponseHelper::success($response);
    }
}