<?php


namespace App\Action\Anime;


use App\Action\Action;
use App\Helper\ResponseHelper;
use MongoDB\BSON\ObjectId;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class UpdateEpisodeName extends Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');

        if($user == null || !in_array('admin', $user['scope']))
        {
            return ResponseHelper::error($response, 'OPERATION_NOT_PERMITTED', [], 403);
        }

        $params = $request->getParsedBody();
        $params['episode'] = (int)$params['episode'];

        $this->mongodb->todonime->animes->updateOne(
            [
                '_id' => new ObjectId($args['id']),
            ],
            ['$set' => [
                "episodes.{$params['episode']}.name" => $params['name']
            ]]
        );

        return ResponseHelper::success($response);
    }
}