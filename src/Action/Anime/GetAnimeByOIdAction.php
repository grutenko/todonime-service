<?php


namespace App\Action\Anime;


use App\Action\Action;
use App\Helper\ResponseHelper;
use MongoDB\BSON\ObjectId;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class GetAnimeByOIdAction extends Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $anime = $this->mongodb->todonime->animes->aggregate([
            [
                '$match' => ['_id' => new ObjectId($args['id'])]
            ],
            [
                '$limit' => 1
            ]
        ])->toArray();

        if (count($anime) == 0) {
            return ResponseHelper::notFound($response, 'Anime' . $args['id'] . ' not found.');
        }

        return ResponseHelper::success($response, $anime[0]);
    }
}