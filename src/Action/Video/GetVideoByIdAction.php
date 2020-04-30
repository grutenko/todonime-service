<?php


namespace App\Action\Video;


use App\Action\Action;
use App\Helper\ResponseHelper;
use MongoDB\BSON\ObjectId;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Class GetVideoByIdAction
 * @package App\Action\Video
 */
class GetVideoByIdAction extends Action
{
    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $video = $this->mongodb->todonime->videos->aggregate([
            [
                '$match' => ['_id' => new ObjectId($args['id'])]
            ],
            [
                '$limit' => 1
            ],
            [
                '$lookup' => [
                    'from' => 'animes',
                    'localField' => 'anime_id',
                    'foreignField' => 'shikimori_id',
                    'as' => 'anime'
                ]
            ],
            [
                '$unset' => 'anime_id'
            ],
            [
                '$unwind' => '$anime'
            ],
            [
                '$addFields' => [
                    'anime' => '$anime',
                ]
            ]
        ])->toArray();

        if (count($video) == 0) {
            return ResponseHelper::notFound($response, 'Video ' . $args['id'] . ' not found.');
        }

        return ResponseHelper::success($response, $video[0]);
    }
}